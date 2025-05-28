<?php

namespace App\Controller\Admin;

use App\Entity\Cinema;
use App\Entity\Movie;
use App\Factory\PagerfantaFactory;
use App\Form\MovieFormType;
use App\Repository\MovieRepository;
use App\Repository\ShowtimeRepository;
use App\Service\MovieService;
use App\Service\TmdbApiService;
use App\Service\UploaderHelper;
use Doctrine\ORM\EntityManagerInterface;
use PhpParser\Builder\Method;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\ExpressionLanguage\Expression;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapQueryParameter;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsCsrfTokenValid;

#[Route("/admin/cinemas/{slug}")]
class MovieController extends AbstractController
{

    public function __construct(private EntityManagerInterface $em) {}

    #[Route('/movies/select-movies', name: 'app_movie_select_movies', methods: ['GET'])]
    public function selectMovies(
        MovieRepository $movieRepository,
        PagerfantaFactory $pagerfantaFactory,
        Cinema $cinema,
        #[MapQueryParameter()] string $q = "",
        #[MapQueryParameter()] int $page = 1,
    ): Response {

        $pagerfanta = $pagerfantaFactory->createTmdbPagerfanta($q, $page);

        $storedTmdbIds = $movieRepository->findTmdbIdsForCinema($cinema);

        return $this->render('movie/select_movies.html.twig', [
            "storedTmdbIds" => $storedTmdbIds,
            "pager" => $pagerfanta,
        ]);
    }

    #[IsCsrfTokenValid(new Expression('"add-tmdbMovie-" ~ args["tmdbId"]'), tokenKey: 'token')]
    #[Route('/movies/add-movie/{tmdbId}', name: 'app_movie_add', methods: ["POST"])]
    public function add(
        Cinema $cinema,
        int $tmdbId,
        MovieService $movieService,
        #[MapQueryParameter] int $page = 1,
        #[MapQueryParameter] string $q = "",
    ): Response {

        $movieService->createMovie($tmdbId, $cinema);
        $this->addFlash("success", "Movie has been added");

        return $this->redirectToRoute("app_movie_select_movies", [
            "slug" => $cinema->getSlug(),
            "page" => $page,
            "q" => $q,
        ]);
    }


    #[Route('/movies/create/{id?}', name: 'app_movie_create', methods: ['POST', 'GET'])]
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
        } elseif ($movie->getTmdbId() !== null) {
            $cachedMovie = $tmdbApiService->cacheMovie($movie->getTmdbId());
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

            if ($form->has('deletePoster')) {

                /** @var SubmitButton $deletePosterButton */
                $deletePosterButton = $form?->get("deletePoster");

                if ($deletePosterButton->isClicked()) {
                    $em->wrapInTransaction(function ($em) use ($uploaderHelper, $movie) {
                        $uploaderHelper->deleteFile($movie->getPosterPath());
                        $movie->setPosterFilename(null);

                        $em->flush();
                    });

                    $this->addFlash('warning', 'Poster successfully deleted!');
                    return $this->redirectToRoute('app_movie_available_movies', [
                        "slug" => $cinema->getSlug()
                    ]);
                }
            };

            $em->persist($movie);
            $em->flush();

            $this->addFlash('success', 'Movie created successfully!');
            return $this->redirectToRoute('app_movie_available_movies', ["slug" => $cinema->getSlug()]);
        }

        return $this->render('movie/create.html.twig', [
            "form" => $form
        ]);
    }



    #[Route('/movies/available-movies', name: 'app_movie_available_movies', methods: ['GET'])]
    public function availableMovies(
        MovieRepository $movieRepository,
        PagerfantaFactory $pagerfantaFactory,
        Cinema $cinema,
        ShowtimeRepository $showtimeRepository,
        #[MapQueryParameter()] int $page = 1,
        #[MapQueryParameter()] string $q = ""
    ): Response {

        $movies = $q
            ? $movieRepository->findBySearchTerm($cinema, $q)
            : $movieRepository->findBySearchTerm($cinema);

        $pagerfanta = $pagerfantaFactory->createAvailableMoviesPagerfanta($movies, $page);

        $scheduledMovieIds = $showtimeRepository->isScheduledShowtimeForMovie($cinema);

        return $this->render('movie/available_movies.html.twig', [
            "pager" => $pagerfanta,
            "scheduledMovieIds" => $scheduledMovieIds
        ]);
    }

    #[IsCsrfTokenValid(new Expression('"delete-movie-" ~ args["movie"].getId()'), tokenKey: 'token')]
    #[Route('/movies/{id}', name: 'app_movie_delete', methods: ["DELETE"])]
    public function addMovieFormats(
        TmdbApiService $tmdbApiService,
        UploaderHelper $uploaderHelper,
        #[MapEntity(mapping: ["slug" => "slug"])] Cinema $cinema,
        #[MapEntity(mapping: ["id" => "id"])] Movie $movie,
        #[MapQueryParameter] int $page = 1,
        #[MapQueryParameter] string $q = "",
    ): Response {

        $this->em->wrapInTransaction(function (EntityManagerInterface $em) use ($tmdbApiService, $movie, $uploaderHelper): void {

            if ($movie->getTmdbId()) {
                $tmdbApiService->deleteMovie($movie->getTmdbId());
            }

            foreach ($movie->getMovieReferences() as $movieReference) {
                $uploaderHelper->deleteFile($movieReference->getFilePath());
                $movieReference->setMovie(null);
                $em->remove($movieReference);
            }

            $em->remove($movie);
            $em->flush();
        });

        $this->addFlash("warning", "Movie has been removed");

        return $this->redirectToRoute("app_movie_available_movies", [
            "page" => $page,
            "q" => $q,
            "slug" => $cinema->getSlug(),
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
