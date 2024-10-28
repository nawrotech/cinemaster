<?php

namespace App\Controller;

use App\Entity\Cinema;
use App\Entity\Movie;
use App\Form\CinemaType;
use App\Repository\CinemaRepository;
use App\Repository\MovieRepository;
use App\Repository\MovieScreeningFormatRepository;
use App\Repository\ScreeningFormatRepository;
use App\Service\MovieScreeningFormatService;
use App\Service\TmdbApiService;
use Doctrine\ORM\EntityManagerInterface;
use Pagerfanta\Doctrine\ORM\QueryAdapter;
use Pagerfanta\Pagerfanta;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapQueryParameter;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted("ROLE_USER")]
#[Route("/cinemas")]
class CinemaController extends AbstractController
{
    #[Route('/', name: 'app_cinema')]
    public function index(
        CinemaRepository $cinemaRepository,
    ): Response {

        $cinemas = $cinemaRepository->findOrderedCinemas($this->getUser());

        return $this->render('cinema/index.html.twig', [
            "cinemas" => $cinemas
        ]);
    }


 #[Route('/create/{slug?}', name: 'app_cinema_create')]
    public function create(
        Request $request,
        EntityManagerInterface $em,
        ?string $slug = null,
        ?Cinema $cinema = null
    ): Response {   

        if (!$cinema) {
            $cinema =  new Cinema();
            $cinema->setOwner($this->getUser());
        }

        $form = $this->createForm(CinemaType::class, $cinema);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $em->persist($cinema);
            $em->flush();

            if (!$slug) {
                $this->addFlash("success", "Cinema created!");
            } else {
                $this->addFlash("success", "Cinema updated!");
            }
            
            return $this->redirectToRoute("app_cinema");
        }

        return $this->render('cinema/create.html.twig', [
            "form" => $form
        ]);
    }

    #[Route('/{slug}/select-movie/{tmdbId}', name: 'app_cinema_select_movie', methods: ["POST"])]
    public function selectMovie(
        TmdbApiService $tmdbApiService,
        Request $request,
        EntityManagerInterface $em,
        MovieRepository $movieRepository,
        Cinema $cinema,
        int $tmdbId
        ): Response
    {
        $submittedToken = $request->get("token");
        if (!$this->isCsrfTokenValid("select-movie", $submittedToken)) {
            $this->addFlash("error", "Invalid CSRF token");
            return $this->redirectToRoute("app_movie");
        }

        if ($request->get("add-movie")) {
            $movieTmdbDto = $tmdbApiService->cacheMovie($tmdbId);

            $movie = new Movie();
            $movie->setTmdbId($tmdbId);
            $movie->setTitle($movieTmdbDto->getTitle());
            $movie->setDurationInMinutes($movieTmdbDto->getDurationInMinutes());
            $movie->setCinema($cinema);
    
            $em->persist($movie);
            $em->flush();
    
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
            "slug" => $cinema->getSlug()
        ]);
   
    }


    #[Route('/{slug?}/add-movies/', name: 'app_cinema_add_movies')]
    public function addMovies(
        MovieRepository $movieRepository,
        ScreeningFormatRepository $screeningFormatRepository,
        MovieScreeningFormatRepository $movieScreeningFormatRepository,
        Cinema $cinema,
        #[MapQueryParameter] ?string $searchTerm = null,
        #[MapQueryParameter] ?int $page = null
    ): Response {

      
        $adapter = new QueryAdapter($movieRepository->findBySearchTerm($searchTerm, true));
        $pagerfanta = new Pagerfanta($adapter);

        $pagerfanta->setMaxPerPage(10);
        $pagerfanta->setCurrentPage($page ?? 1);


        $screeningFormats = $screeningFormatRepository->findBy([
            "cinema" => $cinema
        ]);

        $screeningFormatIdsForMovie = [];
        foreach ($movieRepository->findAll() as $movie) {
            $screeningFormatIdsForMovie[$movie->getId()] = $movieScreeningFormatRepository->findScreeningFormatIdsByMovie($movie, $cinema);
        }

        return $this->render('cinema/movie_screening_formats.html.twig', [
            "cinema" => $cinema,
            "screeningFormats" => $screeningFormats,
            "screeningFormatIdsForMovie" => $screeningFormatIdsForMovie,
            "pager" => $pagerfanta
        ]);
    }

    #[Route('/{slug?}/update-movie-screening/', name: 'app_cinema_update_movie_screening_format', methods: ["POST"])]
    public function updateMovieScreeningFormat(
        MovieRepository $movieRepository,
        Request $request,
        ScreeningFormatRepository $screeningFormatRepository,
        MovieScreeningFormatService $movieScreeningFormatService,
        Cinema $cinema,
        #[MapQueryParameter] int $page,
        #[MapQueryParameter] ?string $searchTerm = null,
    ): Response {

        $movie = $movieRepository->find($request->get("movieId"));

        $screeningFormatIds = array_map("intval", $request->get("screeningFormats", []));
        $screeningFormats = $screeningFormatRepository->findBy(["id" => $screeningFormatIds]);
        
        $redirectRoute = $this->redirectToRoute("app_cinema_add_movies", [
            "slug" => $cinema->getSlug(),
            "page" => $page,
            "searchTerm" => $searchTerm
        ]);

        if (count($screeningFormatIds) !== count($screeningFormats)) {
            $this->addFlash("error", "Invalid screening formats!");

            return $redirectRoute;
        }

        if (!$movie) {
            $this->addFlash("error", "Invalid movie!");
            
            return $redirectRoute;

        }

        $movieScreeningFormatService->update($cinema, $movie, $screeningFormatIds);
        $movieScreeningFormatService->create($cinema, $movie, $screeningFormats);
        
        $this->addFlash("success", "Movie screening formats successfully updated for {$movie->getTitle()}!");

        return $redirectRoute;



    }





  
}
