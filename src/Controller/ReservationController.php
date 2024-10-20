<?php

namespace App\Controller;

use App\Entity\Cinema;
use App\Entity\Showtime;
use App\Form\ReservationType;
use App\Repository\ReservationRepository;
use App\Repository\ReservationSeatRepository;
use App\Service\CartService;
use App\Service\ReservationService;
use App\Service\SeatsService;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route("/cinemas/{slug}/reservations")]
class ReservationController extends AbstractController
{

    #[Route("/{showtime_slug}/add-to-cart", name: "app_reservation_add_to_cart" ,methods: ["POST"])]
    public function addToCart(
        CartService $cartService,
        ReservationSeatRepository $reservationSeatRepository,
        Request $request,
        #[MapEntity(mapping: ["slug" => "slug"])]
        Cinema $cinema,
        #[MapEntity(mapping: ["showtime_slug" => "slug"])]
        Showtime $showtime) {
        
        $reservationSeatId = $request->request->get("reservation_seat_id");
        $session = $request->getSession();

        $seatStatus = $reservationSeatRepository->find($reservationSeatId)
                                                    ->getStatus();

       
        if ($seatStatus !== "available") {
            return $this->redirectToRoute("app_reservation", [
                "slug" => $cinema->getSlug(),
                "showtime_slug" => $showtime->getSlug()
            ]);
        }
        
        if ($request->request->get("reserve")) {
            $cartService->addSeat($reservationSeatId, $session);
        }
        
        if ($request->request->get("cancel")) {
            $cartService->removeSeat($reservationSeatId, $session);
        }
      

        return $this->redirectToRoute("app_reservation", [
            "slug" => $cinema->getSlug(),
            "showtime_slug" => $showtime->getSlug()
        ]);

    }

    #[Route('/{showtime_slug}', name: 'app_reservation')]
    public function index(
        SeatsService $seatService,
        ReservationSeatRepository $reservationSeatRepository,
        ReservationService $reservationService,
        Request $request,
        #[MapEntity(mapping: ["slug" => "slug"])]
        Cinema $cinema,
        #[MapEntity(mapping: ["showtime_slug" => "slug"])]
        Showtime $showtime): Response
    {

        ["roomRows" => $roomRows, "seatsInRow" => $seatsInRow] = $seatService->createGrid(
            $showtime->getScreeningRoom(), 
            $reservationSeatRepository,
            $showtime
        );

        $session = $request->getSession();
        // $session->remove("cart");

        $form = $this->createForm(ReservationType::class, options: [
            "cart" => $session->get("cart")
        ]);


        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $reservationService->lockSeats($session, $form->get("email")->getData());

       
            return $this->redirectToRoute("app_reservation_create", [
                "slug" => $cinema->getSlug(),
                "showtime_slug" => $showtime->getSlug()
            ]);
           
        }
        
        return $this->render('reservation/index.html.twig', [
            "roomRows" => $roomRows,
            "seatsInRow" => $seatsInRow,
            "showtime" => $showtime,
            "form" => $form,
            "cinema" => $cinema
        ]);
    }


    #[Route('/{showtime_slug}/create', name: 'app_reservation_create')]
    public function create(
        ReservationService $reservationService,
        ReservationRepository $reservationRepository,
        Request $request,
        #[MapEntity(mapping: ["slug" => "slug"])]
        Cinema $cinema,
        #[MapEntity(mapping: ["showtime_slug" => "slug"])]
        Showtime $showtime
        ): Response {
            
        $session = $request->getSession();

        // calculate total
        foreach($session->get("cart") as $seat) {

        }

        // IF payment process successful
        // dd($session->get("cart"));

        $reservation = $reservationService->createReservation($session);
        $reservations = $reservationRepository->findBy(["showtime" => $showtime]);
        // dd($reservations);

        // send email wih details

        return $this->redirectToRoute("app_reservation", [
            "slug" => $cinema->getSlug(),
            "showtime_slug" => $showtime->getSlug()
        ]);
    }
 





}
