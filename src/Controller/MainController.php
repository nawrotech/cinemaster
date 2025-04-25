<?php

namespace App\Controller;

use App\Entity\Cinema;
use App\Entity\Movie;
use App\Repository\CinemaRepository;
use App\Service\MovieDataMerger;
use App\Service\MovieService;
use App\Service\ShowtimeService;
use DateTimeImmutable;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;


class MainController extends AbstractController
{
    #[Route('/', name: 'app_main_cinemas')]
    public function cinemas(CinemaRepository $cinemaRepository): Response
    {
        return $this->render("main/cinemas.html.twig", [
            "cinemas" => $cinemaRepository->findAll()
        ]);
    }

    #[Route(
        '/cinemas/{slug?}/showtimes',
        name: 'app_main_cinema_showtimes'
    )]
    public function cinemaShowtimes(
        Cinema $cinema,
        ShowtimeService $showtimeService,
        MovieService $movieService,
    ): Response {

        $movieIds = $showtimeService->getMovieIdsForPublishedShowtimes($cinema);
        if (empty($movieIds)) {
            $this->addFlash('info', 'No movies are currently showing at this cinema.');
            return $this->redirectToRoute('app_main_cinemas');
        }

        $displayMovies = $movieService->getEnrichedMoviesByIds($movieIds);

        $todayUpcomingShowtimes = $showtimeService
            ->getPublishedShowtimesGroupedByMovie($cinema, $movieIds);

        return $this->render("main/cinema_showtimes.html.twig", [
            "cinema" => $cinema,
            "displayMovies" => $displayMovies,
            "todayUpcomingShowtimes" => $todayUpcomingShowtimes,
        ]);
    }

    #[Route('/cinemas/{slug?}/showtimes/{movie_slug}', name: 'app_main_cinema_showtime_details')]
    public function cinemaShowtimeDetails(
        #[MapEntity(mapping: ["slug" => "slug"])] Cinema $cinema,
        #[MapEntity(mapping: ["movie_slug" => "slug"])] Movie $movie,
        ShowtimeService $showtimeService,
        MovieDataMerger $movieDataMerger
    ): Response {

        $showtimesGroupedByDate = $showtimeService->getPublishedShowtimesGroupedByMovieAndDate(
            $cinema,
            $movie,
        );

        $movie = $movieDataMerger->mergeWithApiData($movie);

        return $this->render("main/cinema_showtime_details.html.twig", [
            "cinema" => $cinema,
            "movie" => $movie,
            "showtimesGroupedByDate" => $showtimesGroupedByDate
        ]);
    }
}
