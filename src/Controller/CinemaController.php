<?php

namespace App\Controller;

use App\Entity\Cinema;
use App\Entity\CinemaSeat;
use App\Form\Type\CinemaType;
use App\Repository\CinemaRepository;
use App\Repository\CinemaSeatRepository;
use App\Repository\SeatRepository;
use App\Service\CinemaChangeService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route("/cinemas")]
class CinemaController extends AbstractController
{

    // view created cinemas
    #[Route('/', name: 'app_cinema')]
    public function index(
        CinemaRepository $cinemaRepository,

    ): Response {

        $cinemas = $cinemaRepository->findOrderedCinemas();

        return $this->render('cinema/index.html.twig', [
            "cinemas" => $cinemas
        ]);
    }



    #[Route('/create', name: 'app_cinema_create')]
    public function create(
        Request $request,
        SeatRepository $seatRepository,
        EntityManagerInterface $em
    ): Response {

        $cinema =  new Cinema();

        $form = $this->createForm(CinemaType::class, $cinema);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $roomMaxSize = $form->get('screening_room_size');
            $maxRows = $roomMaxSize->get('max_row')->getData();
            $maxColumns = $roomMaxSize->get('max_column')->getData();

            $seats = $seatRepository->findSeatsInRange(1, $maxRows, 1, $maxColumns);

            foreach ($seats as $seat) {
                $cinemaSeat = new CinemaSeat();
                $cinemaSeat->setSeat($seat);
                $cinemaSeat->setCinema($cinema);

                $em->persist($cinemaSeat);
            }

            $em->persist($cinema);
            $em->flush();

            $this->addFlash("success", "Cinema created!");
            return $this->redirectToRoute("app_cinema");
        }

        return $this->render('cinema/screening_room_max_size.html.twig', [
            "form" => $form
        ]);
    }



    // isGrantedAdmin, slug with cinema name
    #[Route('/{slug}/edit', name: "app_cinema_edit")]
    public function edit(
        Cinema $cinema,
        Request $request,
        CinemaChangeService $cinemaChangeService
    ): Response {

        $form = $this->createForm(CinemaType::class, $cinema);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            try {
                $cinemaChangeService->handleSeatsChange($cinema);
                $this->addFlash('success', 'Cinema updated successfully.');
            } catch (\Exception $e) {
                $this->addFlash('error', 'An error occurred while updating the cinema.');
            } finally {
                return $this->redirectToRoute("app_cinema");
            }
        }

        return $this->render('cinema/edit.html.twig', [
            "form" => $form
        ]);
    }


    // #[Route('/create/movies', name: 'app_cinema_create_movies')]
    // public function createMovies(
    //     EntityManagerInterface $em,
    //     MovieTypeRepository $movieType
    // ): Response {

    //     $movie = new Movie();
    //     $movie->setTitle("Batman");
    //     $movie->setDescription("Story about super orphan");
    //     $movie->addMovieType($movieType->find(3));
    //     $movie->setDurationInMinutes(160);

    //     $em->persist($movie);
    //     $em->flush();


    //     return new Response("movie got created!");
    // }


    // #[Route('/{slug}', name: "app_cinema_details")]
    // public function details(
    //     Cinema $cinema,
    //     EntityManagerInterface $em
    // ): Response {



    //     return $this->render('cinema/details.html.twig', [
    //         "cinema" => $cinema
    //     ]);
    // }


    // #[Route('/create/createSeats', name: "app_cinema_details")]
    // public function seats(EntityManagerInterface $em)
    // {

    //     for ($row = 1; $row <= 25; $row++) {
    //         for ($col = 1; $col <= 25; $col++) {
    //             $seat = new Seat();

    //             $seat->setRowNum($row);
    //             $seat->setColNum($col);
    //             $em->persist($seat);
    //         }
    //     }

    //     $em->flush();

    //     return new Response("Seats created!");
    // }

  
}
