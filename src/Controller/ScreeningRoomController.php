<?php

namespace App\Controller;

use App\Entity\ScreeningRoom;
use App\Entity\ScreeningRoomSeat;
use App\Form\ScreeningRoomType;
use App\Form\SeatLineType;
use App\Repository\ScreeningRoomRepository;
use App\Repository\ScreeningRoomSeatRepository;
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
    public function index(
        ScreeningRoomRepository $screeningRoomRepository
    ): Response {
        $screeningRooms = $screeningRoomRepository->findAll();

        return $this->render('screening_room/index.html.twig', [
            "rooms" => $screeningRooms
        ]);
    }

    #[Route('/create', name: 'app_screening_rooms_create')]
    public function create(
        Request $request,
        EntityManagerInterface $em,
        SeatRepository $seatRepository
    ): Response {


        $screeningRoom =  new ScreeningRoom();


        [$maxRoomSizes] = $seatRepository->findMax();


        $form = $this->createForm(ScreeningRoomType::class, $screeningRoom, [
            "max_room_sizes" => $maxRoomSizes
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($screeningRoom);

            $maxRows = $form->get('screening_room_size')->get('max_row')->getData();
            $maxColumns = $form->get('screening_room_size')->get('max_column')->getData();
            for ($row = 1; $row <= $maxRows; $row++) {
                for ($col = 1; $col <= $maxColumns; $col++) {
                    $roomSeat = new ScreeningRoomSeat();


                    $roomSeat->setScreeningRoom($screeningRoom);
                    // chr(64 + $row) for A,B,C
                    // it is all about displaying
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

    #[Route('/seat/type/{id}', name: 'app_screening_rooms_seat_type_change', methods: ["POST"])]
    public function changeSeatType(
        Request $request,
        ScreeningRoomSeatRepository $screeningRoomSeatRepository,
        EntityManagerInterface $em
    ) {

        $screeningRoomSeat = $screeningRoomSeatRepository
            ->findBySeatId($request->request->get("room_id"), $request->request->get("seat_id"));

        $screeningRoomSeat->setSeatType($request->request->get("seat_type"));
        $em->flush();

        return $this->redirectToRoute(
            "app_screening_rooms_edit",
            ["id" => $request->request->get("room_id")]
        );
    }



    // id for dev, later will be slug with city name
    #[Route('/{id}/edit', name: 'app_screening_rooms_edit')]
    public function edit(
        ScreeningRoom $screeningRoom,
        ScreeningRoomSeatRepository $screeningRoomSeatRepository,
        EntityManagerInterface $em,
        Request $request
    ): Response {

        $roomId = $screeningRoom->getId();

        $roomRows = $screeningRoomSeatRepository
            ->findNumOfRowsForRoom($roomId);
        $seatsInSingleRow = [];

        foreach ($roomRows as $roomRow) {
            $seatsInSingleRow[$roomRow] = $screeningRoomSeatRepository
                ->findSeatsInRow($roomId, $roomRow);
        }

        $form = $this->createForm(SeatLineType::class, options: [
            "allowed_rows" => $roomRows,
        ]);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {

            $seatsInRow = $screeningRoomSeatRepository
                ->findSeatsRangeInRow(
                    $roomId,
                    $form->getData()["row"],
                    $form->getData()["col_start"],
                    $form->getData()["col_end"],
                );

            foreach ($seatsInRow as $screeningRoomSeat) {
                $screeningRoomSeat->setSeatType($form->getData()["seat_type"]);
            }
            $em->flush();

            return $this->redirectToRoute("app_screening_rooms_edit", [
                "id" => $roomId
            ]);
        }


        return $this->render('screening_room/edit.html.twig', [
            "room" => $screeningRoom,
            "roomRows" => $roomRows,
            "rowLine" => $seatsInSingleRow,
            "form" => $form
        ]);
    }
}
