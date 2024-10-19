<?php

namespace App\Controller;

use App\Entity\Cinema;
use App\Repository\ShowtimeRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class MainController extends AbstractController
{
    #[IsGranted("ROLE_USER")]
    #[Route('/', name: 'app_main_movies')]
    public function redirectBack(): Response {

        return $this->redirectToRoute("app_cinema");
    }

    #[Route('/test-comparison', name: 'app_main_comparison')]
    public function comparison(): Response {

        // number of rows may differ, in other words number of 
        $room = [1 => 8, 2 => 8, 3 => 8];
        $updatedRoom = [1 => 6, 2 => 8, 3 => 8, 4 => 10, 5 => 10, 6 => 21];

        $differences = [];

        foreach($updatedRoom as $row => $seats) {
            $diff = null;
            if (!empty($room[$row])) {
                $diff = $room[$row] - $seats;
            } else {
                $diff = $seats;
            }
        
            $differences[$row] = $diff;
        }

        $updatedRoomRowsCount = count($updatedRoom);
        $roomRowsCount = count($room);
        // check which rows have to be killed.
        // but change seat visibility not by range
        // it can be done simply knowing which row should be excluded

        // count($updated) - count($previous)
        // 

        foreach($differences as $row => $diff) {
            if ($diff > 0) {
                // increase number of seats
                // $row, $row, $room[$row], $updatedRoom[$row]

            }

            if ($diff < 0) {
                // decrease number of seats
                // $row, $row, $updatedRoom[$row], $room[$row],

            }
        }



        return new Response("let's dumpt");
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
