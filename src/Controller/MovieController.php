<?php

namespace App\Controller;

use App\Adapter\TmdbAdapter;
use App\Entity\Cinema;
use App\Entity\Movie;
use App\Factory\TmdbAdapterFactory;
use App\Form\MovieFormType;
use App\Form\ScreeningFormatCollectionType;
use App\Repository\MovieRepository;
use App\Repository\MovieScreeningFormatRepository;
use App\Repository\ScreeningFormatRepository;
use App\Service\MovieScreeningFormatService;
use App\Service\TmdbApiService;
use Doctrine\ORM\EntityManagerInterface;
use Pagerfanta\Pagerfanta;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapQueryParameter;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

#[Route("/movies")]
class MovieController extends AbstractController
{
    #[Route('/cinemas/{slug}', name: 'app_movie')]
    public function index(
        MovieRepository $movieRepository,
        TmdbAdapterFactory $tmdbAdapterFactory,
        ScreeningFormatRepository $screeningFormatRepository,
        MovieScreeningFormatRepository $movieScreeningFormatRepository,
        Cinema $cinema,
        #[MapQueryParameter()]
        ? string $q,
        #[MapQueryParameter()]
        ?int $page = 1,
        ): Response
    {
        $endpoint = $q ? "search/movie" : "movie/popular";
        $params = $q ? ["query" => $q] : [];

        $adapter = $tmdbAdapterFactory->create($endpoint, $params);
        $pagerfanta = new Pagerfanta($adapter);

        $currentPage = max(1, $page);
        $pagerfanta->setCurrentPage($currentPage);
        $pagerfanta->setMaxPerPage(TmdbAdapter::MAX_PER_PAGE);

        $screeningFormats = $screeningFormatRepository->findBy([
            "cinema" => $cinema
        ]);

        $screeningFormatIdsForMovie = [];
        foreach ($movieRepository->findAll() as $movie) {
            $screeningFormatIdsForMovie[$movie->getTmdbId()] = $movieScreeningFormatRepository
                                                            ->findScreeningFormatIdsForMovieAtCinema($movie, $cinema);
        }

        $storedTmdbIds = $movieRepository->findTmdbIds($cinema);
        
        return $this->render('movie/index.html.twig', [
            "cinemaSlug" => $cinema->getSlug(),
            "storedTmdbIds" => $storedTmdbIds,
            "pager" => $pagerfanta,
            "screeningFormats" => $screeningFormats,
            "screeningFormatIdsForMovie" => $screeningFormatIdsForMovie            
        ]);
    }

    #[Route('/{slug}/select-movie/{tmdbId}', name: 'app_movie_select', methods: ["POST"])]
    public function select(
        TmdbApiService $tmdbApiService,
        Request $request,
        EntityManagerInterface $em,
        MovieRepository $movieRepository,
        ScreeningFormatRepository $screeningFormatRepository,
        MovieScreeningFormatService $movieScreeningFormatService,
        Cinema $cinema,
        int $tmdbId,
        #[MapQueryParameter] int $page = 1,
        #[MapQueryParameter] ?string $q = null,
        ): Response
    {
        $submittedToken = $request->get("token");
        if (!$this->isCsrfTokenValid("select-movie", $submittedToken)) {
            $this->addFlash("error", "Invalid CSRF token");
            return $this->redirectToRoute("app_movie");
        }

        if ($request->get("add-movie") || $request->get("update-movie")) {
            $movieTmdbDto = $tmdbApiService->cacheMovie($tmdbId);
            
            $screeningFormatIds = array_map("intval", $request->get("screeningFormats", []));
            $screeningFormats = $screeningFormatRepository->findBy(["id" => $screeningFormatIds]);
            
            if (count($screeningFormatIds) !== count($screeningFormats)) {
                $this->addFlash("error", "Invalid screening formats!");
    
                return dd("Welcome");
            }
    
            $movie = $movieRepository->findOneBy(["tmdbId" => $tmdbId]);

            if (!$movie) {
                $movie = new Movie();
                $movie->setTmdbId($tmdbId);
                $movie->setTitle($movieTmdbDto->getTitle());
                $movie->setDurationInMinutes($movieTmdbDto->getDurationInMinutes());
                $movie->setCinema($cinema);
                $em->persist($movie);
                $em->flush();
            }
            
            $movieScreeningFormatService->update($cinema, $movie, $screeningFormatIds);    
            $movieScreeningFormatService->create($cinema, $movie, $screeningFormats); 

            $this->addFlash("success", "Movie has been added");
        }

        if ($request->get("remove-movie")) {
            $tmdbApiService->deleteMovie($tmdbId);

            $movie = $movieRepository->findOneBy(["tmdbId" => $tmdbId]);
            $em->remove($movie);
            $em->flush();

            $this->addFlash("warning", "Movie has been removed");
        }
        
        return $this->redirectToRoute("app_movie", [
            "slug" => $cinema->getSlug(),
            "page" => $page,
            "q" => $q,
            "_fragment" => $request->get("formId")
        ]);
    }
 

    #[Route("/formats/{slug}/create", name: "app_movie_create_screening_formats")]
    public function createScreeningFormats(
        EntityManagerInterface $em,
        Cinema $cinema,
        Request $request): Response
    {
        $form = $this->createForm(ScreeningFormatCollectionType::class, $cinema);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $em->flush();            

            $this->addFlash("success", "Screening formats have been created!");
            
            return $this->redirectToRoute("app_cinema");
        }

        return $this->render('movie/screening_formats_form.html.twig', [
            "form" => $form
        ]);

    }

    #[Route('/create', name: 'app_movie_create')]
    public function create(
        Request $request,
        EntityManagerInterface $em
    ): Response {

        $movie = new Movie();
        $form = $this->createForm(MovieFormType::class, $movie);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $em->persist($movie);
            $em->flush();

            $this->addFlash('success', 'Movie created successfully!');
            return $this->redirectToRoute('app_movie');
        }

        return $this->render('movie/create.html.twig', [
            "form" => $form
        ]);
    }


    #[Route('/{slug}', name: 'app_cinema_movies_details')]
    public function details(): Response
    {
        return $this->render('movie/index.html.twig', [
            'controller_name' => 'MovieController',
        ]);
    }

    #[Route('/{slug}/edit', name: 'app_cinema_movies_edit')]
    public function edit(): Response
    {
        return $this->render('movie/index.html.twig', [
            'controller_name' => 'MovieController',
        ]);
    }
}
