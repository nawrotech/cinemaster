<?php

namespace App\Controller\Admin;

use App\Dto\ScheduledShowtimesFilter;
use App\Dto\ShowtimeDto;
use App\Entity\Cinema;
use App\Entity\ReservationSeat;
use App\Entity\ScreeningRoom;
use App\Entity\Showtime;
use App\Form\ShowtimeType;
use App\Repository\ReservationRepository;
use App\Repository\ScreeningRoomRepository;
use App\Repository\ScreeningRoomSeatRepository;
use App\Repository\ShowtimeRepository;
use App\Service\ShowtimeService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapQueryParameter;
use Symfony\Component\HttpKernel\Attribute\MapQueryString;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsCsrfTokenValid;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Validator\Constraints\Date;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[IsGranted("ROLE_ADMIN")]
#[Route("/admin/cinemas/{slug}")]
class ShowtimeController extends AbstractController
{
    public function __construct(private EntityManagerInterface $em) {}

    #[Route("/showtimes", name: "app_showtime_scheduled_room")]
    public function index(
        Cinema $cinema,
        ShowtimeRepository $showtimeRepository,
        ScreeningRoomRepository $screeningRoomRepository,
        #[MapQueryString] ?ScheduledShowtimesFilter $scheduledShowtimeFilterDto
    ): Response {

        $screeningRoom = $screeningRoomRepository->findOneBy(
            ["name" => $scheduledShowtimeFilterDto?->screeningRoomName]);

        return $this->render('showtime/index.html.twig', [
            "showtimes" => $showtimeRepository->findFiltered(
                $cinema,
                $screeningRoom,
                $scheduledShowtimeFilterDto?->showtimeStartTime,
                $scheduledShowtimeFilterDto?->showtimeEndTime,
                $scheduledShowtimeFilterDto?->movieTitle,
                
            ),
            "availableRoomNames" => $screeningRoomRepository->findDistinctRoomNames($cinema),
        ]);
    }

    #[Route("/screening-rooms/{screening_room_slug}/showtimes/create/{showtime_id?}", name: "app_showtime_create")]
    public function create(
        #[MapEntity(mapping: ["slug" => "slug"])] Cinema $cinema,
        #[MapEntity(mapping: ["screening_room_slug" => "slug"])] ScreeningRoom $screeningRoom,
        Request $request,
        ShowtimeRepository $showtimeRepository,
        ValidatorInterface $validator,
        #[MapQueryParameter()] ?string $showtimeStarting = null,
        #[MapEntity(mapping: ["showtime_id" => "id"])] ?ShowTime $showtime = null,
    ): Response {

        if (!$showtime) {
            $showtime = new Showtime();
            $showtime->setScreeningRoom($screeningRoom);
            $showtime->setCinema($cinema);
        }

        $errors = $validator->validate($showtimeStarting, new Date());
        if (count($errors) > 0) {
            return $this->json([
                "status" => "error",
                "message" => 'Invalid date format. Please use YYYY-MM-DD format.'
            ], Response::HTTP_BAD_REQUEST);
        }

        $showtimeStartsAtDate = (new \DateTime($showtimeStarting))->format("Y-m-d") 
                ?? new \DateTimeImmutable()->format("Y-m-d"); 

        $showtimes = $showtimeRepository->findBy(["cinema" => $cinema]);

        $form = $this->createForm(ShowtimeType::class, $showtime);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $this->em->persist($showtime);
            $this->em->flush();

            $this->addFlash("success", "Showtime created successfully!");

            return $this->redirectToRoute("app_showtime_create", [
                "slug" => $cinema->getSlug(),
                "screening_room_slug" => $screeningRoom->getSlug(),
                "showtimeStarting" => $showtime->getStartsAt()->format("Y-m-d")
            ]);
        }

        return $this->render('showtime/create.html.twig', [
            "form" => $form,
            "showtimes" => $showtimes,
            "screeningRoom" => $screeningRoom,
            "showtimeStartsAtDate" => $showtimeStartsAtDate,
            "openHour" => $cinema->getOpenTime()->format("G"),
            "closeHour" => $cinema->getCloseTime()->format("G"),
        ]);
    }



    #[IsCsrfTokenValid('publish_showtime', tokenKey: 'token')]
    #[Route("/showtimes/publish/by-showtime-id/{showtime_id?}", name: "app_showtime_publish", methods: ["POST"])]
    public function publish(
        #[MapEntity(mapping: ["slug" => "slug"])]
        Cinema $cinema,
        #[MapEntity(mapping: ["showtime_id" => "id"])]
        ShowTime $showtime,
        Request $request,
        ReservationRepository $reservationRepository,
        ShowtimeService $showtimeService
    ): Response {

        if ($request->request->get("published") === "0" &&
            !$reservationRepository->hasReservations($showtime)) {
            $showtime->setPublished(false);
            $this->em->flush();
            $this->addFlash("success", "Show has been successfully unpublished");
            return $this->redirectToRoute("app_showtime_scheduled_room", [
                "slug" => $cinema->getSlug()
            ]);
        } elseif ($request->request->get("published") === "1") {
            $showtimeService->publishShowtime($showtime);
            $this->addFlash("success", "Show has been successfully published");
            return $this->redirectToRoute("app_showtime_scheduled_room", [
                "slug" => $cinema->getSlug()
            ]);
        } else {
            $this->addFlash("danger", "Show has reservations and cannot be unpublished");
            return $this->redirectToRoute("app_showtime_scheduled_room", [
                "slug" => $cinema->getSlug()
            ]);
        }

    }

    #[Route("/showtimes/publish/by-date/{date}",
     name: "app_showtime_publish_for_date", 
     requirements: ["date" => "\d{4}-\d{2}-\d{2}"],
     methods: ["POST", 'GET'])]
    public function publishForDate(
        #[MapEntity(mapping: ["slug" => "slug"])]
        Cinema $cinema,
        ScreeningRoomSeatRepository $screeningRoomSeatRepository,
        ShowtimeRepository $showtimeRepository,
        ValidatorInterface $validator,
        string $date
    ): Response {

        $errors = $validator->validate($date, new Date());
        if (count($errors) > 0) {
            return $this->json([
                "status" => "error",
                "message" => 'Invalid date format. Please use YYYY-MM-DD format.'
            ], Response::HTTP_BAD_REQUEST);
        }

        $showtimes = $showtimeRepository->findFiltered($cinema, date: $date, isPublished: false);
        if (!(count($showtimes) > 0)) {
            $this->addFlash('info', 'No unpublished showtimes found for this date');
            return $this->redirectToRoute('app_showtime_showtime_axis', [
                'slug' => $cinema->getSlug()
            ]);
        }

        $batchSize = 20;
        $i = 0;

        $this->em->wrapInTransaction(function ($em) use ($showtimes, $screeningRoomSeatRepository, $i, $batchSize) {
            foreach ($showtimes as $showtime) {
                $seats = $screeningRoomSeatRepository
                            ->findBy(["screeningRoom" => $showtime->getScreeningRoom()]);
                
                foreach ($seats as $seat) {
                    $reservationSeat = new ReservationSeat();
                    $reservationSeat->setShowtime($showtime);
                    $reservationSeat->setSeat($seat);
                    $reservationSeat->setStatus($seat->getStatus());
                    $this->em->persist($reservationSeat);
                }
                
                $showtime->setPublished(true);
                $em->persist($showtime);
                
                if (++$i % $batchSize === 0) {
                    $em->flush();
                    $em->clear(ReservationSeat::class);
                }
            }
        });

        $this->addFlash("success", "Successfully published showtimes for $date");

        return $this->redirectToRoute("app_showtime_showtime_axis", [
            "slug" => $cinema->getSlug()
        ]);
    }

    #[Route("/showtimes/{date?}",
        name: "app_showtime_scheduled_showtimes_in_cinema",
        requirements: ["date" => "\d{4}-\d{2}-\d{2}"])]
    public function scheduledShowtimesOnDateInCinema(
        #[MapEntity(mapping: ["slug" => "slug"])] Cinema $cinema,
        ShowtimeRepository $showtimeRepository,
        ValidatorInterface $validator,
        string $date,
    ) {
        $errors = $validator->validate($date, new Date());
        if (count($errors) > 0) {
            return $this->json([
                "status" => "error",
                "message" => 'Invalid date format. Please use YYYY-MM-DD format.'
            ], Response::HTTP_BAD_REQUEST);
        }

        $showtimes = $showtimeRepository->findFiltered(
            cinema: $cinema,
            date: $date,
            includeScreeningRoomName: true
        );

        $cinemaShowtimes = [];
        foreach ($showtimes as $showtime) {
            $cinemaShowtimes[$showtime['screeningRoomName']][] = $showtime[0];
        }

        foreach ($cinemaShowtimes as &$roomShowtimes) {
            foreach ($roomShowtimes as &$roomShowtime) {
                $roomShowtime = ShowtimeDto::fromEntity($roomShowtime);
            }
        }
        
        return $this->json($cinemaShowtimes);
    }

    #[Route("/screening-rooms/{screening_room_slug}/showtimes/{date?}", 
        name: "app_showtime_scheduled_showtimes",
        requirements: ["date" => "\d{4}-\d{2}-\d{2}"])]
    public function scheduledShowtimesOnDateForScreeningRoom(
        #[MapEntity(mapping: ["slug" => "slug"])] Cinema $cinema,
        ShowtimeRepository $showtimeRepository,
        ValidatorInterface $validator,
        string $date,
        #[MapEntity(mapping: ["screening_room_slug" => "slug"])] ?ScreeningRoom $screeningRoom = null,
    ) {
        $errors = $validator->validate($date, new Date());
        if (count($errors) > 0) {
            return $this->json([
                "status" => "error",
                "message" => 'Invalid date format. Please use YYYY-MM-DD format.'
            ], Response::HTTP_BAD_REQUEST);
        }

        $showtimes = $showtimeRepository->findFiltered(
            cinema: $cinema,
            screeningRoom: $screeningRoom,
            date: $date,
        );

        $showtimes = array_map(function (Showtime $showtime) {
            return ShowtimeDto::fromEntity($showtime);
        }, $showtimes);

        return $this->json($showtimes);
    }

    #[Route("/scheduled-showtime-axis", name: "app_showtime_showtime_axis")]
    public function showtimeAxis(
        Cinema $cinema,
        ScreeningRoomRepository $screeningRoomRepository
    ) {
        $screeningRooms = $screeningRoomRepository->findBy(["cinema" => $cinema]);

        return $this->render("showtime/cinema_showtime_axis.html.twig", [
            "screeningRooms" => $screeningRooms,
            "cinema" => $cinema
        ]);
    }
}
