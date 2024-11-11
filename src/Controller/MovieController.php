<?php

namespace App\Controller;

use App\Entity\Cinema;
use App\Entity\Movie;
use App\Factory\PagerfantaFactory;
use App\Form\MovieFormType;
use App\Form\ScreeningFormatCollectionType;
use App\Repository\MovieRepository;
use App\Repository\ShowtimeRepository;
use App\Service\TmdbApiService;
use App\Service\UploaderHelper;
use Doctrine\ORM\EntityManagerInterface;
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
        PagerfantaFactory $pagerfantaFactory,
        Cinema $cinema,
        #[MapQueryParameter()] string $q = "",
        #[MapQueryParameter()] int $page = 1,
        ): Response
    {
        
        $pagerfanta = $pagerfantaFactory->createTmdbPagerfanta($q, $page);

        $storedTmdbIds = $movieRepository->findTmdbIdsForCinema($cinema);
        
        return $this->render('movie/select_movies.html.twig', [
            "storedTmdbIds" => $storedTmdbIds,
            "pager" => $pagerfanta,
        ]);
    }

    #[Route('/movies/add-movie/{tmdbId}', name: 'app_movie_add', methods: ["POST"])]
    public function add(
        TmdbApiService $tmdbApiService,
        Request $request,
        EntityManagerInterface $em,
        Cinema $cinema,
        int $tmdbId,
        #[MapQueryParameter] int $page = 1,
        #[MapQueryParameter] string $q = "",
        ): Response
    {
        $submittedToken = $request->get("token");
        if (!$this->isCsrfTokenValid("add-tmdbMovie", $submittedToken)) {
            $this->addFlash("danger", "Invalid CSRF token");
            return $this->redirectToRoute("app_movie_select_movies", [
                "slug" => $cinema->getSlug()
            ]);
        }

        $movieTmdbDto = $tmdbApiService->cacheMovie($tmdbId);
        
        $movie = new Movie();
        $movie->setTmdbId($tmdbId);
        $movie->setTitle($movieTmdbDto->getTitle());
        $movie->setDurationInMinutes($movieTmdbDto->getDurationInMinutes());
        $movie->setCinema($cinema);
        $em->persist($movie);
        $em->flush();

        $this->addFlash("success", "Movie has been added");
        
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
                $posterFilename = $uploaderHelper->uploadMoviePoster($posterFile, $movie?->getPosterFilename());
                $movie->setPosterFilename($posterFilename);
            }


            $submittedForm = $request->get($form->getName());
           
            if ($submittedForm["deletePoster"] ?? null) {

                $em->wrapInTransaction(function($em) use($uploaderHelper, $movie) {
                    $uploaderHelper->deleteFile($movie->getPosterPath());
                    $movie->setPosterFilename(null);

                    $em->flush();
                });
     

                $this->addFlash('warning', 'Poster successfully deleted!');
                return $this->redirectToRoute('app_movie_available_movies', ["slug" => $cinema->getSlug()]);      
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
     PagerfantaFactory $pagerfantaFactory,
     Cinema $cinema,
     ShowtimeRepository $showtimeRepository,
     #[MapQueryParameter()] int $page = 1,
     #[MapQueryParameter()] string $q = ""
 ): Response
 {
     $movies = $q 
        ? $movieRepository->findBySearchTerm($cinema, $q) 
        : $movieRepository->findBy(["cinema" => $cinema]);
     
    $pagerfanta = $pagerfantaFactory->createAvailableMoviesPagerfanta($movies, $page);

     $isScheduledShowtimeForMovie = [];
     foreach ($movies as $movie) {
        $isScheduledShowtimeForMovie[$movie->getId()] =  $showtimeRepository->isScheduledShowtimeForMovie($movie);
     }

     return $this->render('movie/available_movies.html.twig', [
         "pager" => $pagerfanta,
         "isScheduledShowtimeForMovie" => $isScheduledShowtimeForMovie
     ]);
 }


 #[Route('/movies/{id}', name: 'app_movie_delete', methods: ["DELETE"])]
 public function addMovieFormats(
     Request $request,
     EntityManagerInterface $em,
     TmdbApiService $tmdbApiService,
     #[MapEntity(mapping:["slug" => "slug"])] Cinema $cinema,
     #[MapEntity(mapping:["id" => "id"])] Movie $movie,
     #[MapQueryParameter] int $page = 1,
     #[MapQueryParameter] string $q = "",
     ): Response
 {
     $submittedToken = $request->get("token");
     if (!$this->isCsrfTokenValid("delete-movie-token", $submittedToken)) {
         $this->addFlash("error", "Invalid CSRF token");
         return $this->redirectToRoute("app_movie");
     }

    $tmdbApiService->deleteMovie($movie->getTmdbId());

    $em->remove($movie);
    $em->flush();

    $this->addFlash("warning", "Movie has been removed");

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
