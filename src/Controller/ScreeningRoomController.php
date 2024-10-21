<?php

namespace App\Controller;

use App\Entity\Cinema;
use App\Entity\ScreeningRoom;
use App\Entity\ScreeningRoomSeat;
use App\Enum\ScreeningRoomSeatType;
use App\Form\ScreeningRoomType;
use App\Form\ScreeningRoomTypesType;
use App\Form\SeatLineType;
use App\Repository\ScreeningRoomRepository;
use App\Repository\ScreeningRoomSeatRepository;
use App\Repository\SeatRepository;
use App\Service\SeatsService;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route("/cinemas/{slug}/screening-rooms")]
class ScreeningRoomController extends AbstractController
{


 
    // plus filtering
    #[Route('/', name: 'app_screening_room')]
    public function index(
        Cinema $cinema,
        ScreeningRoomRepository $screeningRoomRepository,
    ): Response {

        $screeningRooms = $screeningRoomRepository->findBy(["cinema" => $cinema]);

        return $this->render('screening_room/index.html.twig', [
            "rooms" => $screeningRooms,
            "cinema" => $cinema
        ]);
    }


    #[Route('/types', name: 'app_screening_room_category')]
    public function createType(
        Request $request,
        EntityManagerInterface $em,
        Cinema $cinema,
        ): Response {



        $form = $this->createForm(ScreeningRoomTypesType::class, $cinema);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {


            $em->flush();
        }

        return $this->render("screening_room/create_type.html.twig", [
            "form" => $form
        ]);


    }


    #[Route('/create', name: 'app_screening_room_create')]
    public function create(
        Request $request,
        EntityManagerInterface $em,
        SeatRepository $seatRepository,
        Cinema $cinema,
    ): Response {

        if ($request->query->get("ajaxCall")) {
            return $this->json([
                "maxSeatsPerRow" => $cinema->getMaxSeatsPerRow(),
                "maxRows" => $cinema->getMaxRows()
            ]);
        }

        $screeningRoom =  new ScreeningRoom();
        $screeningRoom->setCinema($cinema);

        $form = $this->createForm(ScreeningRoomType::class, $screeningRoom, [
            "max_room_sizes" => [
                "maxRows" => $cinema->getMaxRows(),
                "maxSeatsPerRow" => $cinema->getMaxSeatsPerRow()
            ]
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $seatsPerRow = $form->get("seatsPerRow")->getData();
            $rowsAndSeats = array_combine(range(1, count($seatsPerRow)), $seatsPerRow);
            
    
            $em->wrapInTransaction(function($em) use($rowsAndSeats, $seatRepository, $screeningRoom) {
                foreach ($rowsAndSeats as $row => $lastSeatInRow) {
                 
                    $seatsRange = $seatRepository->findSeatsInRange($row, $row, 1, $lastSeatInRow);
                    
                    foreach ($seatsRange as $seat) {
                        $screeningRoomSeat = new ScreeningRoomSeat();
                        $screeningRoomSeat->setScreeningRoom($screeningRoom);
                        $screeningRoomSeat->setSeat($seat);
                        $em->persist($screeningRoomSeat);
                    }
                }
    
                $em->persist($screeningRoom);
                $em->flush();
            });

      
            return $this->redirectToRoute("app_screening_room", [
                "slug" => $cinema->getSlug()
            ]);
        }

        return $this->render('screening_room/create.html.twig', [
            "form" => $form,
            "cinema_slug" => $cinema->getSlug()
        ]);
    }


    #[Route('/seat/type/{id}', name: 'app_screening_room_seat_type_change', methods: ["POST"])]
    public function changeSeatType(
        Request $request,
        ScreeningRoomSeat $screeningRoomSeat,
        EntityManagerInterface $em
    ) {

        $seatType = $request->getPayload()->get("seatType");
        if (!in_array($seatType, ScreeningRoomSeatType::getValuesArray())) {

            $this->addFlash("error", "Disallowed type for the value");

            return $this->redirectToRoute(
                "app_screening_room_edit",
                [
                    "screening_room_slug" => $request->getPayload()->get("screeningRoomSlug"),
                    "slug" => $request->getPayload()->get("cinemaSlug")
                ]
            );
        }
        $screeningRoomSeat->setType($seatType);


        $seatStatus = $request->getPayload()->get("seatStatus") ? "available" : "unavailable";
        $screeningRoomSeat->setStatus($seatStatus);
        $em->flush();

        return $this->redirectToRoute(
            "app_screening_room_edit",
            [
                "screening_room_slug" => $request->getPayload()->get("screeningRoomSlug"),
                "slug" => $request->getPayload()->get("cinemaSlug")

            ]
        );
    }



    #[Route("/{screening_room_slug}/edit", name: 'app_screening_room_edit')]
    public function edit(
        #[MapEntity(mapping: ["slug" => "slug"])]
        Cinema $cinema,
        #[MapEntity(mapping: ["screening_room_slug" => "slug"])]
        ScreeningRoom $screeningRoom,
        SeatsService $seatsService,
        ScreeningRoomSeatRepository $screeningRoomSeatRepository,
        EntityManagerInterface $em,
        Request $request
    ): Response {

        [
            "roomRows" => $roomRows,
            "seatsInRow" => $seatsInRow
        ] = $seatsService->createGrid($screeningRoom, $screeningRoomSeatRepository);
        
        $form = $this->createForm(SeatLineType::class, options: [
            "allowed_rows" => $roomRows,
            "allowed_seat_types" => ScreeningRoomSeatType::getValuesArray()
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $seatsInRow = $screeningRoomSeatRepository
                ->findSeatsInRange(
                    $screeningRoom,
                    $form->get("row")->getData(),
                    $form->get("row")->getData(),
                    $form->get("colStart")->getData(),
                    $form->get("colEnd")->getData(),
                );

            foreach ($seatsInRow as $screeningRoomSeat) {
                $screeningRoomSeat->setSeatType($form->get("seatType")->getData());
            }

            $em->flush();

            return $this->redirectToRoute("app_screening_room_edit", [
                "screening_room_slug" => $screeningRoom->getSlug(),
                "slug" => $cinema->getSlug()
            ]);
        }

        return $this->render('screening_room/edit.html.twig', [
            "room" => $screeningRoom,
            "roomRows" => $roomRows,
            "rowLine" => $seatsInRow,
            "form" => $form,
            "cinema" => $cinema,
            "seatTypes" => ScreeningRoomSeatType::getValuesArray()
        ]);
    }
}
