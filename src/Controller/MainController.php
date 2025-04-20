<?php

namespace App\Controller;

use App\Entity\Cinema;
use App\Repository\CinemaRepository;
use App\Repository\ShowtimeRepository;
use App\Service\MovieService;
use App\Service\ShowtimeService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
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

    #[Route('/cinemas/{slug?}/showtimes/{date?}', 
    name: 'app_main_cinema_showtimes',
    requirements: ["date" => "\d{4}-\d{2}-\d{2}"])]
    public function cinemaShowtimes(
        Cinema $cinema,
        ShowtimeRepository $showtimeRepository,
        ShowtimeService $showtimeService,
        MovieService $movieService,
        ValidatorInterface $validator,
        ?string $date = null
    ): Response {

        $errors = $validator->validate($date, new Date());
        if (count($errors) > 0) {
            $this->addFlash('error', 'Invalid date format. Please use YYYY-MM-DD format.');
            return $this->redirectToRoute('app_main_cinema_showtimes', [
                'slug' => $cinema->getSlug(),
                'date' => new \DateTimeImmutable('now')->format('Y-m-d')
            ]);
        }

        $date = $date ?? new \DateTimeImmutable('now')->format('Y-m-d');

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

    
}
