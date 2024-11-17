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
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapQueryParameter;
use Symfony\Component\Routing\Attribute\Route;

// #[Route("/cinemas/{slug}/showtimes")]
class ShowtimeController extends AbstractController
{
    #[Route("/cinemas/{slug}/showtimes/list", name: "app_showtime")]
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

        $screeningRoom = $screeningRoomRepository->findOneBy(["name" => $screeningRoomName]);
        return $this->render('showtime/index.html.twig', [
            "showtimes" => $showtimeRepository->findFiltered(
                $cinema,
                $screeningRoom,
                $showtimeStartTime,
                $showtimeEndTime,
                $movieTitle
            ),
            "availableRoomNames" => $screeningRoomRepository->findDistinctRoomNames($cinema)
        ]);
    }

    #[Route("/cinemas/{slug}/screening-rooms/{screening_room_slug}/showtimes/create/{showtime_id?}", name: "app_showtime_create")]
    public function create(
        #[MapEntity(mapping: ["slug" => "slug"])]
        Cinema $cinema,
        #[MapEntity(mapping: ["screening_room_slug" => "slug"])]
        ScreeningRoom $screeningRoom,
        EntityManagerInterface $em,
        Request $request,
        ShowtimeRepository $showtimeRepository,
        #[MapEntity(mapping: ["showtime_id" => "id"])]
        ?ShowTime $showtime = null,
    ): Response {

        if (!$showtime) {
            $showtime = new Showtime();
            $showtime->setScreeningRoom($screeningRoom);
            $showtime->setCinema($cinema);
        }

        $startsAt = $showtime?->getStartsAt()?->format("Y-m-d") ?? (new \DateTime())->format("Y-m-d");
        $showtimes = $showtimeRepository->findBy(["cinema" => $cinema]);

        $form = $this->createForm(ShowtimeType::class, $showtime);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
                        
            $em->persist($showtime);
            $em->flush();

            $this->addFlash("success", "Showtime created successfully!");
    
            return $this->redirectToRoute("app_showtime_create", [
                "slug" => $cinema->getSlug(),
                "screening_room_slug" => $screeningRoom->getSlug(),
                "showtime_id" => $showtime->getId()
            ]);

        }

        return $this->render('showtime/create.html.twig', [
            "form" => $form,
            "showtimes" => $showtimes,
            "screeningRoom" => $screeningRoom,
            "startsAt" => $startsAt
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

     
        $em->wrapInTransaction(function ($em) use($showtime, $showtimeRoomSeats) {
            foreach ($showtimeRoomSeats as $showtimeRoomSeat) {
                $reservationSeat = new ReservationSeat();
                $reservationSeat->setShowtime($showtime);
                $reservationSeat->setSeat($showtimeRoomSeat);
                $reservationSeat->setStatus($showtimeRoomSeat->getStatus());
                $em->persist($reservationSeat);
            }
            $showtime->setPublished(true);
            $em->flush();
        });
        
          
        $this->addFlash("success", "Show has been successfully published");
 
        return $this->redirectToRoute("app_showtime", [
            "slug" => $cinema->getSlug()
        ]);
    }

    #[Route("/cinemas/{slug}/screening-rooms/{screening_room_slug}/showtimes/{date?}", name: "app_showtime_scheduled_showtimes")] 
    public function scheduledShowtimesOnDateForScreeningRoom(
        #[MapEntity(mapping: ["slug" => "slug"])] Cinema $cinema,
        #[MapEntity(mapping: ["screening_room_slug" => "slug"])] ScreeningRoom $screeningRoom,
        ShowtimeRepository $showtimeRepository,
        string $date
    ) {

        $showtimes = $showtimeRepository->findFiltered(
            cinema: $cinema, 
            screeningRoom: $screeningRoom,
            showtimeStartTime: $date,
            showtimeEndTime: $date,
        );

        $showtimes = array_map(function(Showtime $showtime) {
            return [
                "id" => $showtime->getId(),
                "movieTitle" => $showtime->getMovieScreeningFormat()->getMovie()->getTitle(),
                "screeningFormat" => $showtime->getMovieScreeningFormat()->getScreeningFormat()->getDisplayScreeningFormat(),
                "startsAt" => $showtime->getStartsAt()->format(DateTime::ATOM),
                "endsAt" => $showtime->getEndsAt()->format(DateTime::ATOM),
                "advertisementTimeInMinutes" => $showtime->getAdvertisementTimeInMinutes(),
                "maintenanceTimeInMinutes" => $showtime->getScreeningRoom()->getMaintenanceTimeInMinutes(),
                "movieDurationInMinutes" => $showtime->getMovieScreeningFormat()->getMovie()->getDurationInMinutes(),
                "durationInMinutes" => $showtime->getDuration()
            ];
        }, $showtimes);


        return $this->json($showtimes);
    }

    #[Route("/{screening_room_slug}/showtime-axis", name: "app_showtime_showtime_axis")]
    public function showtimeAxis(
        #[MapEntity(mapping: ["slug" => "slug"])] Cinema $cinema,
        #[MapEntity(mapping: ["screening_room_slug" => "slug"])] ScreeningRoom $screeningRoom,
    ) {
        // dd($screeningRoom, $cinema);

        return $this->render("showtime/showtimeAxis.html.twig");
    }


}
