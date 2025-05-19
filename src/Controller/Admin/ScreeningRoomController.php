<?php

namespace App\Controller\Admin;

use App\Entity\Cinema;
use App\Entity\ScreeningRoom;
use App\Entity\ScreeningRoomSeat;
use App\Enum\ScreeningRoomSeatType;
use App\Form\ScreeningRoomType;
use App\Form\SeatRowType;
use App\Repository\CinemaRepository;
use App\Repository\ScreeningRoomRepository;
use App\Repository\ScreeningRoomSetupRepository;
use App\Service\ScreeningRoomSeatService;
use App\Service\SeatService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\ExpressionLanguage\Expression;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsCsrfTokenValid;

#[Route("/admin/cinemas/{slug}/screening-rooms")]
class ScreeningRoomController extends AbstractController
{

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

    #[Route('/create', name: 'app_screening_room_create')]
    public function create(
        Request $request,
        ScreeningRoomSetupRepository $screeningRoomSetupRepository,
        Cinema $cinema,
        SeatService $seatService
    ): Response {

        if ($request->query->get("ajaxCall")) {
            return $this->json([
                "maxSeatsPerRow" => $cinema->getMaxSeatsPerRow(),
                "maxRows" => $cinema->getMaxRows()
            ]);
        }

        if (!$screeningRoomSetupRepository->hasActiveSetupForCinema($cinema)) {
            $this->addFlash("danger", "Add screening room setups before creating screening room!");

            return $this->redirectToRoute("app_cinema_details", [
                "slug" => $cinema->getSlug()
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

            $priceTier = $form->get('priceTier')->getData();

            $seatsPerRow = $form->get("seatsPerRow")->getData();
            $rowsAndSeats = array_combine(range(1, count($seatsPerRow)), $seatsPerRow);

            $seatService->assignSeatsToScreeningRoom($screeningRoom, $rowsAndSeats, $priceTier);

            $this->addFlash("success", "Screening room has been created!");
            return $this->redirectToRoute("app_cinema_details", [
                "slug" => $cinema->getSlug()
            ]);
        }

        return $this->render('screening_room/create.html.twig', [
            "form" => $form,
            "cinema_slug" => $cinema->getSlug()
        ]);
    }


    #[IsCsrfTokenValid(new Expression('"edit-seat-" ~ args["screeningRoomSeat"].getId()'), tokenKey: 'token')]
    #[Route('/seat/type/{id}', name: 'app_screening_room_seat_update', methods: ["POST"])]
    public function changeSeatType(
        Request $request,
        ScreeningRoomSeat $screeningRoomSeat,
        CinemaRepository $cinemaRepository,
        ScreeningRoomRepository $screeningRoomRepository,
        EntityManagerInterface $em
    ) {

        $cinema = $cinemaRepository->findOneBy(["slug" => $request->getPayload()->get("cinemaSlug")]);
        if (!$cinema) {
            throw $this->createNotFoundException('Cinema not found.');
        }

        $screeningRoom = $screeningRoomRepository->findOneBy(["slug" => $request->getPayload()->get("screeningRoomSlug")]);
        if (!$screeningRoom) {
            throw $this->createNotFoundException('Screening Room not found.');
        }

        $seatType = ScreeningRoomSeatType::tryFrom($request->getPayload()->get("seatType"));
        if ($seatType == null) {
            throw $this->createNotFoundException('Screening room type not found.');

        }

        $screeningRoomSeat->setType($seatType);
        $seatStatus = $request->getPayload()->get("seatStatus") ? "available" : "unavailable";

        $screeningRoomSeat->setStatus($seatStatus);
        $em->flush();

        $this->addFlash('success', 'Seat successfully updated');

        return $this->redirectToRoute(
            "app_screening_room_edit",
            [
                "screening_room_slug" => $screeningRoom->getSlug(),
                "slug" => $cinema->getSlug()

            ]
        );
    }

    #[IsCsrfTokenValid(new Expression('"delete-screening-room-" ~ args["screeningRoom"].getId()'), tokenKey: 'token')]
    #[Route('/delete/{id}', name: 'app_screening_room_delete', methods:["DELETE"])]
    public function deleteScreeningRoom(
        string $slug,
        ScreeningRoom $screeningRoom,
        ScreeningRoomRepository $screeningRoomRepository,
        EntityManagerInterface $em) {

        if ($screeningRoomRepository->hasShowtimes($screeningRoom)) {
            $this->addFlash('danger', 'Cannot delete screening room with associated showtimes.');
            return $this->redirectToRoute('app_cinema_details', ['slug' => $slug]);
        }

        $cinemaSlug = $screeningRoom->getCinema()->getSlug();
        $screeningRoom->setActive(false);
        $em->flush();

        $this->addFlash("warning", "Screening room has been removed!");

        return $this->redirectToRoute("app_cinema_details", [
            "slug" => $cinemaSlug
        ]);

    }

    #[Route("/edit/{screening_room_slug}", name: 'app_screening_room_edit')]
    public function edit(
        #[MapEntity(mapping: ["slug" => "slug"])]
        Cinema $cinema,
        #[MapEntity(mapping: ["screening_room_slug" => "slug"])]
        ScreeningRoom $screeningRoom,
        ScreeningRoomSeatService $screeningRoomSeatService,
        Request $request
    ): Response {

        $groupedSeats = $screeningRoomSeatService->groupSeatsForLayout($screeningRoom);
        $roomRows = array_keys($groupedSeats);
        
        $form = $this->createForm(SeatRowType::class, options: [
            "allowed_rows" => $roomRows,
            'cinema' => $cinema
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $screeningRoomSeatService->updateSeatTypeForRow(
                $screeningRoom,
                $form->get("rowStart")->getData(),
                $form->get("rowEnd")->getData(),
                $form->get("firstSeatInRow")->getData(),
                $form->get("lastSeatInRow")->getData(),
                $form->get("seatType")->getData(),
                $form->get('priceTier')->getData()
            );

            $this->addFlash('success', 'Seats have been updated!');

            return $this->redirectToRoute("app_screening_room_edit", [
                "screening_room_slug" => $screeningRoom->getSlug(),
                "slug" => $cinema->getSlug()
            ]);
        }

        return $this->render('screening_room/edit.html.twig', [
            "room" => $screeningRoom,
            "form" => $form,
            "cinema" => $cinema,
            "groupedSeats" => $groupedSeats
        ]);
    }
}
