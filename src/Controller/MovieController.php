<?php

namespace App\Controller;

use App\Adapter\TmdbAdapter;
use App\Entity\Cinema;
use App\Entity\Movie;
use App\Entity\MovieScreeningFormat;
use App\Entity\ScreeningFormat;
use App\Factory\TmdbAdapterFactory;
use App\Form\MovieFormType;
use App\Form\ScreeningFormatCollectionType;
use App\Repository\MovieRepository;
use App\Repository\MovieScreeningFormatRepository;
use App\Repository\ScreeningFormatRepository;
use App\Service\MovieDataMerger;
use App\Service\MovieScreeningFormatService;
use App\Service\TmdbApiService;
use App\Service\UploaderHelper;
use Doctrine\ORM\EntityManagerInterface;
use Pagerfanta\Adapter\ArrayAdapter;
use Pagerfanta\Pagerfanta;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapQueryParameter;
use Symfony\Component\Routing\Attribute\Route;

#[Route("/cinemas/{slug}")]
class MovieController extends AbstractController
{
    #[Route('/movies/select-movies' , name: 'app_movie_select_movies')]
    public function selectMovies(
        MovieRepository $movieRepository,
        TmdbAdapterFactory $tmdbAdapterFactory,
        ScreeningFormatRepository $screeningFormatRepository,
        MovieScreeningFormatRepository $movieScreeningFormatRepository,
        Cinema $cinema,
        #[MapQueryParameter()] string $q = "",
        #[MapQueryParameter()] int $page = 1,
        ): Response
    {
 
        $endpoint = $q ? "search/movie" : "movie/now_playing";
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
                                                            ->findScreeningFormatsForMovie($movie);
        }
        $storedTmdbIds = $movieRepository->findTmdbIdsForCinema($cinema);
        
        return $this->render('movie/index.html.twig', [
            "storedTmdbIds" => $storedTmdbIds,
            "pager" => $pagerfanta,
            "screeningFormats" => $screeningFormats,
            "screeningFormatIdsForMovie" => $screeningFormatIdsForMovie            
        ]);
    }

    #[Route('/movies/add-movie/{tmdbId}', name: 'app_movie_add', methods: ["POST"])]
    public function add(
        TmdbApiService $tmdbApiService,
        Request $request,
        EntityManagerInterface $em,
        MovieRepository $movieRepository,
        Cinema $cinema,
        int $tmdbId,
        #[MapQueryParameter] int $page = 1,
        #[MapQueryParameter] string $q = "",
        ): Response
    {
        $submittedToken = $request->get("token");
        if (!$this->isCsrfTokenValid("add-movie", $submittedToken)) {
            $this->addFlash("error", "Invalid CSRF token");
            return $this->redirectToRoute("app_movie_select_movies", [
                "slug" => $cinema->getSlug()
            ]);
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
        

        return $this->redirectToRoute("app_movie_select_movies", [
            "slug" => $cinema->getSlug(),
            "page" => $page,
            "q" => $q,
            "_fragment" => $request->get("formId")
        ]);

    }

 

    #[Route("/movies/formats/create", name: "app_movie_create_screening_formats")]
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

    #[Route('/movies/create/{id?}', name: 'app_movie_create')]
    public function create(
        Request $request,
        EntityManagerInterface $em,
        TmdbApiService $tmdbApiService,
        UploaderHelper $uploaderHelper,
        #[MapEntity(mapping: ["slug" => "slug"])] Cinema $cinema,
        #[MapEntity(mapping: ["id" => "id"])] ?Movie $movie = null
    ): Response {

        if (!$movie) {
            $movie = new Movie();
            $movie->setCinema($cinema);
        } else {
            if ($movie->getTmdbId()) {
                $cachedMovie = $tmdbApiService->cacheMovie($movie->getTmdbId());
            }
        }

        $form = $this->createForm(MovieFormType::class, $movie, [
            "defaults" => $cachedMovie ?? null
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $posterFile = $form->get("poster")->getData();

            if ($posterFile) {
                $posterFilename = $uploaderHelper->uploadMoviePoster($posterFile, $movie->getPosterFilename());
                $movie->setPosterFilename($posterFilename);
            }
            
            $em->persist($movie);
            $em->flush();

            $this->addFlash('success', 'Movie created successfully!');
            return $this->redirectToRoute('app_movie_available_movies', ["slug" => $cinema->getSlug()]);
        }

        return $this->render('movie/create.html.twig', [
            "form" => $form
        ]);
    }



 #[Route('/movies/available-movies', name: 'app_movie_available_movies')]
 public function availableMovies(
     MovieRepository $movieRepository,
     MovieDataMerger $movieDataMerger,
     ScreeningFormatRepository $screeningFormatRepository,
     MovieScreeningFormatRepository $movieScreeningFormatRepository,
     Cinema $cinema,
     #[MapQueryParameter()] int $page = 1,
     #[MapQueryParameter()] string $q = ""
 ): Response
 {
     $movies = $q 
        ? $movieRepository->findBySearchTerm($cinema, $q) 
        : $movieRepository->findBy(["cinema" => $cinema]);
     
     $mergedMovies = array_map(function(Movie $movie) use($movieDataMerger) {
         return $movieDataMerger->mergeWithApiData($movie);
     }, $movies);

     $adapter = new ArrayAdapter($mergedMovies);
     $pagerfanta = new Pagerfanta($adapter);

     $pagerfanta->setMaxPerPage(10);
     $pagerfanta->setCurrentPage($page);

     $screeningFormats = $screeningFormatRepository->findBy([
         "cinema" => $cinema
     ]);

     $screeningFormatIdsForMovie = [];
     foreach ($movies as $movie) {
         $screeningFormatIdsForMovie[$movie->getId()] = $movieScreeningFormatRepository
                                                         ->findScreeningFormatsForMovie($movie);
     }


     $singleMovie = $movieRepository->find(94);

     return $this->render('movie/available_movies.html.twig', [
         "pager" => $pagerfanta,
         "screeningFormats" => $screeningFormats,
         "screeningFormatIdsForMovie" => $screeningFormatIdsForMovie,
         "singleMovie" => $singleMovie,
     ]);
 }


 #[Route('/movies/add-movie-formats/{id}', name: 'app_movie_add_movie_formats', methods: ["POST"])]
 public function addMovieFormats(
     Request $request,
     MovieScreeningFormatService $movieScreeningFormatService,
     #[MapEntity(mapping:["slug" => "slug"])] Cinema $cinema,
     #[MapEntity(mapping:["id" => "id"])] Movie $movie,
     #[MapQueryParameter] int $page = 1,
     #[MapQueryParameter] string $q = "",
     ): Response
 {
     $submittedToken = $request->get("token");
     if (!$this->isCsrfTokenValid("add-movie-formats-token", $submittedToken)) {
         $this->addFlash("error", "Invalid CSRF token");
         return $this->redirectToRoute("app_movie");
     }

    $screeningFormatIds = array_map("intval", $request->get("screeningFormats", []));

    // $movieScreeningFormatService->update($cinema, $movie, $screeningFormatIds);    
    $movieScreeningFormatService->create($cinema, $movie, $screeningFormatIds); 

    $this->addFlash("success", "Screening formats has been applied!");
     
     return $this->redirectToRoute("app_movie_available_movies", [
         "page" => $page,
         "q" => $q,
         "slug" => $cinema->getSlug(),
         "_fragment" => $request->get("formId")
     ]);

 }
   

    #[Route('/edit', name: 'app_cinema_movies_edit')]
    public function edit(): Response
    {
        return $this->render('movie/index.html.twig', [
            'controller_name' => 'MovieController',
        ]);
    }
}
