<?php

namespace App\Controller;

use App\Entity\Cinema;
use App\Entity\Seat;
use App\Form\Type\CinemaType;
use App\Repository\CinemaRepository;
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
    public function index(CinemaRepository $cinemaRepository): Response
    {
        $cinemas = $cinemaRepository->findOrderedCinemas();

        return $this->render('cinema/index.html.twig', [
            "cinemas" => $cinemas
        ]);
    }

    // isGrantedAdmin
    // cinema name could be used in the url for cinema reference
    #[Route('/create', name: 'app_cinema_create')]
    public function create(
        Request $request,
        EntityManagerInterface $em
    ): Response {

        $cinema =  new Cinema();

        $form = $this->createForm(CinemaType::class, $cinema);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $maxRows = $form->get('screening_room_size')->get('max_row')->getData();
            $maxColumns = $form->get('screening_room_size')->get('max_column')->getData();

            for ($row = 1; $row <= $maxRows; $row++) {
                for ($col = 1; $col <= $maxColumns; $col++) {
                    $seat = new Seat();
                    // chr(64 + $row) for A,B,C
                    $seat->setRowNum(chr(64 + $row));
                    $seat->setColNum($col);

                    $cinema->addSeat($seat);

                    $em->persist($seat);
                }
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


    #[Route('/{slug}/edit', name: "app_cinema_edit")]
    public function details(): Response
    {


        return $this->render('cinema/details.html.twig', []);
    }


    // isGrantedAdmin, slug with cinema name
    #[Route('/{slug}/edit', name: "app_cinema_details")]
    public function edit(): Response
    {
        return $this->render('cinema/details.html.twig', []);
    }
}
