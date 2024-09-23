<?php

namespace App\Controller;

use App\Entity\Cinema;
use App\Entity\ScreeningRoom;
use App\Entity\ScreeningRoomSeat;
use App\Form\ScreeningRoomType;
use App\Form\SeatLineType;
use App\Repository\CinemaRepository;
use App\Repository\CinemaSeatRepository;
use App\Repository\ScreeningRoomRepository;
use App\Repository\ScreeningRoomSeatRepository;
use App\Repository\SeatRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route("/cinema/{slug}/rooms")]
class ScreeningRoomController extends AbstractController
{
    // plus filtering
    #[Route('/', name: 'app_screening_rooms')]
    public function index(
        Cinema $cinema,
        ScreeningRoomRepository $screeningRoomRepository
    ): Response {

        $screeningRooms = $screeningRoomRepository->findAll(["cinema" => $cinema]);

        return $this->render('screening_room/index.html.twig', [
            "rooms" => $screeningRooms,
            "cinema" => $cinema
        ]);
    }

    #[Route('/create', name: 'app_screening_rooms_create')]
    public function create(
        Request $request,
        EntityManagerInterface $em,
        CinemaRepository $cinemaRepository,
        Cinema $cinema,
        CinemaSeatRepository $cinemaSeatsRepository
    ): Response {

        $screeningRoom =  new ScreeningRoom();
        $screeningRoom->setCinema($cinema);
        $maxRoomSizes = $cinemaRepository->findMax($cinema);

        $form = $this->createForm(ScreeningRoomType::class, $screeningRoom, [
            "max_room_sizes" => $maxRoomSizes
        ]);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {


            $maxRows = $form->get('screening_room_size')->get('max_row')->getData();
            $maxColumns = $form->get('screening_room_size')->get('max_column')->getData();

            $cinemaSeats = $cinemaSeatsRepository->findSeatsInRange($maxRows, $maxColumns, $cinema);

            foreach ($cinemaSeats as $cinemaSeat) {
                $screeningRoomSeat = new ScreeningRoomSeat();
                $screeningRoomSeat->setScreeningRoom($screeningRoom);
                $screeningRoomSeat->setSeat($cinemaSeat);

                $em->persist($screeningRoomSeat);
            }

            $em->persist($screeningRoom);
            $em->flush();

            return $this->redirectToRoute("app_screening_rooms", [
                "slug" => $cinema->getSlug()
            ]);
        }


        return $this->render('screening_room/create.html.twig', [
            "form" => $form
        ]);
    }

    #[Route('/{room-slug}', name: 'app_screening_rooms_details')]
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
            [
                "id" => $request->request->get("room_id"),
                "slug" => $request->request->get("cinema_name")
            ]
        );
    }



    // id for dev, later will be slug with room name
    #[Route('/{id}/edit', name: 'app_screening_rooms_edit')]
    public function edit(
        Cinema $cinema,
        ScreeningRoom $screeningRoom,
        ScreeningRoomSeatRepository $screeningRoomSeatRepository,
        EntityManagerInterface $em,
        Request $request
    ): Response {


        $roomRows = $screeningRoomSeatRepository
            ->findNumOfRowsForRoom($screeningRoom);

        $seatsInSingleRow = [];

        foreach ($roomRows as $roomRow) {
            $seatsInSingleRow[$roomRow] = $screeningRoomSeatRepository
                ->findSeatsInRow($screeningRoom, $roomRow);
        }

        $form = $this->createForm(SeatLineType::class, options: [
            "allowed_rows" => $roomRows,
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $seatsInRow = $screeningRoomSeatRepository
                ->findSeatsRangeInRow(
                    $screeningRoom,
                    $form->getData()["row"],
                    $form->getData()["col_start"],
                    $form->getData()["col_end"],
                );

            foreach ($seatsInRow as $screeningRoomSeat) {
                $screeningRoomSeat->setSeatType($form->getData()["seat_type"]);
            }
            $em->flush();

            return $this->redirectToRoute("app_screening_rooms_edit", [
                "id" => $screeningRoom->getid(),
                "slug" => $cinema->getName()
            ]);
        }

        // dd($seatsInSingleRow);
        return $this->render('screening_room/edit.html.twig', [
            "room" => $screeningRoom,
            "roomRows" => $roomRows,
            "rowLine" => $seatsInSingleRow,
            "form" => $form,
            "cinema" => $cinema
        ]);
    }
}
