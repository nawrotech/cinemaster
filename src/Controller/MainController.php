<?php

namespace App\Controller;

use App\Entity\Cinema;
use App\Repository\CinemaRepository;
use App\Repository\MovieRepository;
use App\Repository\ShowtimeRepository;
use App\Service\MovieDataMerger;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class MainController extends AbstractController
{
    #[Route('/cinemas', name: 'app_main_cinemas')]
    public function cinemas(CinemaRepository $cinemaRepository): Response {

        $cinemas = $cinemaRepository->findAll();

        return $this->render("main/cinemas.html.twig", [
            "cinemas" => $cinemas
        ]);

    }

    #[Route('/inky-emails', name: 'app_main_inky_emails')]
    public function inkyEmails(): Response {


        return $this->render("reset_password/email.html.twig");

    }

    

    #[Route('/cinemas/{slug?}/showtimes', name: 'app_main_cinema_showtimes')]
    public function cinemaShowtimes(
        Cinema $cinema, 
        ShowtimeRepository $showtimeRepository,
        MovieRepository $movieRespository,
        MovieDataMerger $movieDataMerger
        ): Response {

        
        $movieIds = $showtimeRepository->findMovieIdsForPublishedShowtimes($cinema);
        $movies = $movieRespository->findBy(["id" => $movieIds]);
        $displayMovies = [];
        foreach ($movies as $movie) {
            $displayMovies[$movie->getId()] = $movieDataMerger->mergeWithApiData($movie);
        }

        $now = new \DateTimeImmutable();
        $endOfTheDay = new \DateTimeImmutable("23:59:59");
        $movieShowtimesPlannedForToday = [];
        foreach ($movieIds as $movieId) {
            $movieShowtimesPlannedForToday[$movieId] = $showtimeRepository
                    ->findScheduledShowtimesForMovieBetweenDates($movieId, $now, $endOfTheDay);
        }  
        

        return $this->render("main/cinema_showtimes.html.twig", [
            "cinema" => $cinema,
            "displayMovies" => $displayMovies,
            "movieShowtimesPlannedForToday" => $movieShowtimesPlannedForToday
        ]);
    }

    

  
}
