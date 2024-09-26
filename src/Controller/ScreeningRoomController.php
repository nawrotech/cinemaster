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
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route("/cinema/{slug}/rooms")]
class ScreeningRoomController extends AbstractController
{
    #[Route("/api/max-rows-constraint", name: 'app_max_row_fields_constraint')]
    public function maxRowsConstraint(
        Cinema $cinema,
        CinemaRepository $cinemaRepository
    ) {

        $cinemaSizeMaxes = $cinemaRepository->findMax($cinema);

        return $this->json($cinemaSizeMaxes);
    }

    // plus filtering
    #[Route('/', name: 'app_screening_rooms')]
    public function index(
        Cinema $cinema,
        ScreeningRoomRepository $screeningRoomRepository,
    ): Response {

        $screeningRooms = $screeningRoomRepository->findAll(["cinema" => $cinema]);
        // dd($screeningRoom->getScreeningRoomSeats()->count());
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


            // because when giving a row specific value
            // indexes changes to 5,6,7,8
            $rowsAndSeats = array_values($form->get("seats_per_row")->getData());

            foreach ($rowsAndSeats as $row => $lastSeatInRow) {
                $row = $row + 1;
                $seatsRange = $cinemaSeatsRepository->findSeatsInGivenrange($cinema, $row, $row, 1, $lastSeatInRow);

                foreach ($seatsRange as $cinemaSeat) {
                    $screeningRoomSeat = new ScreeningRoomSeat();
                    $screeningRoomSeat->setScreeningRoom($screeningRoom);
                    $screeningRoomSeat->setSeat($cinemaSeat);
                    $em->persist($screeningRoomSeat);

                    // redundant, not even an owner,
                    $screeningRoom->addScreeningRoomSeat($screeningRoomSeat);
                }
            }


            $em->persist($screeningRoom);
            $em->flush();

            return $this->redirectToRoute("app_screening_rooms", [
                "slug" => $cinema->getSlug()
            ]);
        }

        return $this->render('screening_room/create.html.twig', [
            "form" => $form,
            "cinema_slug" => $cinema->getSlug()
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
                "screening_room_slug" => $request->request->get("screening_room_slug"),
                "slug" => $request->request->get("cinema_slug")
            ]
        );
    }



    // id for dev, later will be slug with room name
    #[Route("/{screening_room_slug}/edit", name: 'app_screening_rooms_edit')]
    public function edit(
        #[MapEntity(mapping: ["slug" => "slug"])]
        Cinema $cinema,
        #[MapEntity(mapping: ["screening_room_slug" => "slug"])]
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
                ->findSeatsInRange(
                    $screeningRoom,
                    $form->getData()["row"],
                    $form->getData()["row"],
                    $form->getData()["col_start"],
                    $form->getData()["col_end"],
                );

            foreach ($seatsInRow as $screeningRoomSeat) {
                $screeningRoomSeat->setSeatType($form->getData()["seat_type"]);
            }

            $em->flush();

            return $this->redirectToRoute("app_screening_rooms_edit", [
                "screening_room_slug" => $screeningRoom->getSlug(),
                "slug" => $cinema->getSlug()
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
