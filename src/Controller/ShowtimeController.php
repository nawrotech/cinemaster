<?php

namespace App\Controller;

use App\Entity\Cinema;
use App\Entity\ReservationSeat;
use App\Entity\ScreeningRoom;
use App\Entity\Showtime;
use App\Form\ShowtimeType;
use App\Repository\ScreeningRoomRepository;
use App\Repository\ScreeningRoomSeatRepository;
use App\Repository\ShowtimeRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapQueryParameter;
use Symfony\Component\Routing\Attribute\Route;

#[Route("/cinemas/{slug}/showtimes")]
class ShowtimeController extends AbstractController
{
    #[Route("/", name: "app_showtime")]
    public function index(
        Cinema $cinema,
        ShowtimeRepository $showtimeRepository,
        ScreeningRoomRepository $screeningRoomRepository,
        #[MapQueryParameter()] ?string $screeningRoomName,
        #[MapQueryParameter()] ?string $showtimeStartTime,
        #[MapQueryParameter()] ?string $showtimeEndTime,
        #[MapQueryParameter()] ?string $movieTitle,
        ): Response
    {

        return $this->render('showtime/index.html.twig', [
            "showtimes" => $showtimeRepository->findFiltered(
                $cinema,
                $screeningRoomName,
                $showtimeStartTime,
                $showtimeEndTime,
                $movieTitle
            ),
            "availableRoomNames" => $screeningRoomRepository->findDistinctRoomNames($cinema)
        ]);
    }

    #[Route("/create/{screening_room_slug}/{showtime_id?}", name: "app_showtime_create")]
    public function create(
        #[MapEntity(mapping: ["slug" => "slug"])]
        Cinema $cinema,
        #[MapEntity(mapping: ["screening_room_slug" => "slug"])]
        ScreeningRoom $screeningRoom,
        EntityManagerInterface $em,
        Request $request,
        #[MapEntity(mapping: ["showtime_id" => "id"])]
        ?ShowTime $showtime = null,
    ): Response {

        if (!$showtime) {
            $showtime = new Showtime();
            $showtime->setScreeningRoom($screeningRoom);
            $showtime->setCinema($cinema);
        }

        $form = $this->createForm(ShowtimeType::class, $showtime);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            
            $em->persist($showtime);
            $em->flush();

            $this->addFlash("success", "Showtime created successfully!");

            return $this->redirectToRoute("app_showtime", [
                "slug" => $cinema->getSlug()
            ]);
        }

        return $this->render('showtime/create.html.twig', [
            "form" => $form
        ]);
    }

    #[Route("/publish/{showtime_id?}", name: "app_showtime_publish", methods: ["POST"])]
    public function publish(
        #[MapEntity(mapping: ["slug" => "slug"])]
        Cinema $cinema,
        #[MapEntity(mapping: ["showtime_id" => "id"])]
        ShowTime $showtime,
        ScreeningRoomSeatRepository $screeningRoomSeatRepository,
        EntityManagerInterface $em
        ): Response {
        
        $showtimeRoomSeats = $screeningRoomSeatRepository->findBy(
            ["screeningRoom" => $showtime->getScreeningRoom()]);

        // transaction
        foreach ($showtimeRoomSeats as $showtimeRoomSeat) {
            $reservationSeat = new ReservationSeat();
            $reservationSeat->setShowtime($showtime);
            $reservationSeat->setSeat($showtimeRoomSeat);
            $em->persist($reservationSeat);
        }
        
        // dd($showtimeRoomSeats);
        $showtime->setPublished(true);
        $em->flush();

        $this->addFlash("success", "Show has been successfully published");
 
        return $this->redirectToRoute("app_showtime", [
            "slug" => $cinema->getSlug()
        ]);
    }
}
