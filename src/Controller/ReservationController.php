<?php

namespace App\Controller;

use App\Entity\Cinema;
use App\Entity\Showtime;
use App\Form\ReservationType;
use App\Repository\ReservationSeatRepository;
use App\Service\SeatsService;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route("/cinemas/{slug}/reservations")]
class ReservationController extends AbstractController
{

    #[Route("/add-to-cart", name: "app_reservation_add_to_cart" ,methods: ["POST"])]
    public function addToCart(
        Cinema $cinema,
        ReservationSeatRepository $reservationSeatRepository,
        Request $request) {
        
        $session = $request->getSession();
        $reservationSeatId = $request->request->get("reservation_seat_id");
        $cartSeats = $session->get("cart", []);

        if ($request->request->get("reserve")) {
            if ($reservationSeatId && !in_array($reservationSeatId, $cartSeats)) {
                $cartSeats[] = $reservationSeatId;
            }
            $cartSeats = $session->set("cart", $cartSeats);
        }
        
        if ($request->request->get("cancel")) {
            $cartSeats = array_filter($cartSeats, fn(string $id) => $id !== $reservationSeatId);
            $cartSeats = $session->set("cart", $cartSeats);
        }
   
        $showtime = $reservationSeatRepository
            ->find($reservationSeatId)->getShowtime();

        return $this->redirectToRoute("app_reservation", [
            "slug" => $cinema->getSlug(),
            "showtime_slug" => $showtime->getSlug()
        ]);

    }

    #[Route('/{showtime_slug}', name: 'app_reservation')]
    public function index(
        SeatsService $seatService,
        ReservationSeatRepository $reservationSeatRepository,
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
        // dd($session);
        // dd($session->get("cart", []));

        $form = $this->createForm(ReservationType::class);
        $form->handleRequest($request);
  
        if ($form->isSubmitted() && $form->isValid()) {
            // dd($form);
            // dd("hehe:");
        }

        
        return $this->render('reservation/index.html.twig', [
            "roomRows" => $roomRows,
            "seatsInRow" => $seatsInRow,
            "showtime" => $showtime,
            "form" => $form,
            "cinema" => $cinema
        ]);
    }

 





}
