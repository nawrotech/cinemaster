<?php

namespace App\Controller;

use App\Entity\ScreeningRoom;
use App\Entity\ScreeningRoomSeat;
use App\Form\ScreeningRoomType;
use App\Repository\SeatRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route("/cinema/rooms")]
class ScreeningRoomController extends AbstractController
{
    // plus filtering
    #[Route('/', name: 'app_screening_rooms')]
    public function index(): Response
    {
        return $this->render('screening_room/index.html.twig', [
            'controller_name' => 'ScreeningRoomController',
        ]);
    }

    #[Route('/create', name: 'app_screening_rooms_create')]
    public function create(
        Request $request,
        EntityManagerInterface $em,
        SeatRepository $seatRepository
    ): Response {
        $screeningRoom =  new ScreeningRoom();

        $form = $this->createForm(ScreeningRoomType::class, $screeningRoom);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $em->persist($screeningRoom);

            $maxRows = $form->get('screening_room_size')->get('max_rows')->getData();
            $maxColumns = $form->get('screening_room_size')->get('max_columns')->getData();
            for ($row = 1; $row <= $maxRows; $row++) {
                for ($col = 1; $col <= $maxColumns; $col++) {
                    $roomSeat = new ScreeningRoomSeat();
                    $roomSeat->setScreeningRoom($screeningRoom);
                    // chr(64 + $row) for A,B,C
                    $seat = $seatRepository->findOneBy(["rowNum" => $row, "colNum" => $col]);

                    $roomSeat->setSeat($seat);

                    $em->persist($roomSeat);
                }
            }

            $em->persist($roomSeat);
            $em->flush();

            return $this->redirectToRoute("app_screening_rooms_create");
        }


        return $this->render('screening_room/create.html.twig', [
            "form" => $form
        ]);
    }

    #[Route('/{slug}', name: 'app_screening_rooms_details')]
    public function details(): Response
    {
        return $this->render('screening_room/index.html.twig', [
            'controller_name' => 'ScreeningRoomController',
        ]);
    }




    #[Route('/{slug}/edit', name: 'app_screening_rooms_edit')]
    public function edit(): Response
    {
        return $this->render('screening_room/index.html.twig', [
            'controller_name' => 'ScreeningRoomController',
        ]);
    }
}
