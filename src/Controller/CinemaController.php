<?php

namespace App\Controller;

use App\Entity\Cinema;
use App\Entity\CinemaSeat;
use App\Form\Type\CinemaType;
use App\Repository\CinemaRepository;
use App\Repository\SeatRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route("/cinema")]
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

        // dd($seatRepository->findMax());

        $form = $this->createForm(CinemaType::class, $cinema);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $maxRows = $form->get('screening_room_size')->get('max_row')->getData();
            $maxColumns = $form->get('screening_room_size')->get('max_column')->getData();


            $seats = $seatRepository->findSeatsInRange($maxRows, $maxColumns);

            foreach ($seats as $seat) {
                $cinemaSeat = new CinemaSeat();
                $cinemaSeat->setSeat($seat);
                $cinemaSeat->setCinema($cinema);

                $cinema->addCinemaSeat($cinemaSeat);

                $em->persist($cinemaSeat);
            }
            $em->persist($cinema);
            $em->flush();

            $this->addFlash("success", "Cinema created");

            return $this->redirectToRoute("app_cinema");
        }

        return $this->render('cinema/screening_room_max_size.html.twig', [
            "form" => $form
        ]);
    }


    #[Route('/{slug}', name: "app_cinema_details")]
    public function details(
        Cinema $cinema
    ): Response {


        return $this->render('cinema/details.html.twig', [
            "cinema" => $cinema
        ]);
    }

    // isGrantedAdmin




    // isGrantedAdmin, slug with cinema name
    // #[Route('/{slug}/edit', name: "app_cinema_edit")]
    // public function edit(): Response
    // {


    //     return $this->render('cinema/details.html.twig', []);
    // }
}
