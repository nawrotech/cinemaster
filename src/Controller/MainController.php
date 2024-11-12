<?php

namespace App\Controller;

use App\Entity\Cinema;
use App\Repository\CinemaRepository;
use App\Repository\ShowtimeRepository;
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

    #[Route('/cinemas{slug?}/showtimes', name: 'app_main_cinema_showtimes')]
    public function cinemaShowtimes(Cinema $cinema, ShowtimeRepository $showtimeRepository): Response {

        dd($cinema);
        // $showtimes = $showtimeRepository->


        return $this->redirectToRoute("app_cinema");
    }

    

    #[Route('/cinemas/{slug}/main', name: 'app_main')]
    public function index(
        ShowtimeRepository $showtimeRepository,
        Cinema $cinema
        ): Response
    {
        $distinctMovies = $showtimeRepository->findDistinctMovies($cinema);
        $movieFormats = [];
        foreach($distinctMovies as $distinctMovie) {
            $movieFormats[$distinctMovie["id"]] = $showtimeRepository->findForMovie($distinctMovie["id"], $cinema);
        }   

        return $this->render('main/index.html.twig', [
            "distinctMovies" => $distinctMovies,
            "movieFormats" => $movieFormats,
            "cinema" => $cinema
        ]);
    }

  
}
