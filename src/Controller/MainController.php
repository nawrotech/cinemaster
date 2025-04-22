<?php

namespace App\Controller;

use App\Entity\Cinema;
use App\Entity\Movie;
use App\Repository\CinemaRepository;
use App\Repository\ShowtimeRepository;
use App\Service\MovieService;
use App\Service\ShowtimeService;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapQueryParameter;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Validator\Constraints\Date;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class MainController extends AbstractController
{
    #[Route('/', name: 'app_main_cinemas')]
    public function cinemas(CinemaRepository $cinemaRepository): Response
    {

        return $this->render("main/cinemas.html.twig", [
            "cinemas" => $cinemaRepository->findAll()
        ]);
    }

    #[Route('/cinemas/{slug?}/showtimes', 
    name: 'app_main_cinema_showtimes')]
    public function cinemaShowtimes(
        Cinema $cinema,
        ShowtimeRepository $showtimeRepository,
        ShowtimeService $showtimeService,
        MovieService $movieService,
        ValidatorInterface $validator,
        #[MapQueryParameter()] ?string $date = null
    ): Response {

        $errors = $validator->validate($date, new Date());
        $date = (($date === null || count($errors) > 0) ?
                 new \DateTimeImmutable('now')->format('Y-m-d') : 
                 new \DateTimeImmutable($date)->format('Y-m-d'));

        $movieIds = $showtimeRepository->findMovieIdsForPublishedShowtimes($cinema, $date);
        if (empty($movieIds)) {
            $this->addFlash('info', 'No movies are currently showing at this cinema.');
            return $this->redirectToRoute('app_main_cinemas');
        }

        $displayMovies = $movieService->getEnrichedMoviesByIds($movieIds);

        $todayUpcomingShowtimes = $showtimeService
            ->getPublishedShowtimesGroupedByMovie($cinema, $movieIds, $date);

        return $this->render("main/cinema_showtimes.html.twig", [
            "cinema" => $cinema,
            "displayMovies" => $displayMovies,
            "todayUpcomingShowtimes" => $todayUpcomingShowtimes,
            'date' => $date
        ]);
    }

    #[Route('/cinemas/{slug?}/showtimes/{movie_slug}', 
    name: 'app_main_cinema_showtime_details')]
    public function cinemaShowtimeDetails(
        #[MapEntity(mapping: ["slug" => "slug"])] Cinema $cinema,
        #[MapEntity(mapping: ["movie_slug" => "slug"])] Movie $movie
    ): Response {
        return $this->render("main/cinema_showtime_details.html.twig", [
            "cinema" => $cinema,
            "movie" => $movie
        ]);
    }

    
}
