<?php

namespace App\Controller;

use App\Entity\Cinema;
use App\Entity\Reservation;
use App\Entity\Showtime;
use App\Form\ReservationType;
use App\Repository\ReservationSeatRepository;
use App\Service\CartService;
use App\Service\Mailer;
use App\Service\PriceTierExtractorService;
use App\Service\ReservationSeatService;
use App\Service\ReservationService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\UriSigner;
use Symfony\Component\HttpKernel\Attribute\MapQueryParameter;
use Symfony\Component\Routing\Attribute\Route;

#[Route("/cinemas/{slug}/reservations")]
class ReservationController extends AbstractController
{
    #[Route('/{showtime_slug}', name: 'app_reservation_reserve_showtime')]
    public function reserveShowtime(
        ReservationService $reservationService,
        ReservationSeatService $reservationSeatService,
        Request $request,
        #[MapEntity(mapping: ["slug" => "slug"])] Cinema $cinema,
        #[MapEntity(mapping: ["showtime_slug" => "slug"])] Showtime $showtime,
        PriceTierExtractorService $priceTierExtractorService
    ): Response {

        $priceTiers = $priceTierExtractorService->getShowtimePriceTiers($showtime);

        $groupedReservationSeats = $reservationSeatService->groupSeatsForLayout($showtime);

        $session = $request->getSession();
        $form = $this->createForm(ReservationType::class, options: [
            "cart" => $session->get("cart")
        ]);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $reservationService->lockSeats($session, $form->get("email")->getData());

            return $this->redirectToRoute("app_reservation_create_reservation", [
                "slug" => $cinema->getSlug(),
                "showtime_slug" => $showtime->getSlug()
            ]);
        }

        return $this->render('reservation/index.html.twig', [
            "groupedReservationSeats" => $groupedReservationSeats,
            "showtime" => $showtime,
            "form" => $form,
            "cinema" => $cinema,
            'priceTiers' => $priceTiers
        ]);
    }



    #[Route("/{showtime_slug}/add-to-cart", name: "app_reservation_add_to_cart", methods: ["POST"])]
    public function addToCart(
        CartService $cartService,
        ReservationSeatRepository $reservationSeatRepository,
        Request $request,
        #[MapEntity(mapping: ["slug" => "slug"])]
        Cinema $cinema,
        #[MapEntity(mapping: ["showtime_slug" => "slug"])]
        Showtime $showtime
    ) {

        $reservationSeatId = $request->get("reservation_seat_id");
        $session = $request->getSession();

        $selectedSeat  = $reservationSeatRepository->find($reservationSeatId);
        $isNotAvailable =  $selectedSeat->getStatus() !== "available" || $selectedSeat->getStatusLockedExpiresAt() > new \DateTimeImmutable();

        if ($isNotAvailable) {
            return $this->redirectToRoute("app_reservation_reserve_showtime", [
                "slug" => $cinema->getSlug(),
                "showtime_slug" => $showtime->getSlug()
            ]);
        }

        $showtimeId = $showtime->getId();
        if ($request->get("reserve")) {
            $cartService->addSeat($reservationSeatId, $session, $showtimeId);
        }

        if ($request->get("cancel")) {
            $cartService->removeSeat($reservationSeatId, $session, $showtimeId);
        }

        return $this->redirectToRoute("app_reservation_reserve_showtime", [
            "slug" => $cinema->getSlug(),
            "showtime_slug" => $showtime->getSlug()
        ]);
    }

    #[Route("/{id}/validation-form", name: 'app_reservation_show_validation_form')]
    public function showValidationForm(
        #[MapEntity(mapping: ["id" => "id"])] Reservation $reservation,
        Request $request,
        UriSigner $uriSigner,
    ): Response {

        $url = $request->getUri();
        $uriSignatureIsValid = $uriSigner->check($url);

        if ($uriSignatureIsValid === false || !$reservation) {
            return $this->json([
                'status' => 'error',
                'message' => 'Invalid token or reservation',
            ], Response::HTTP_FORBIDDEN);
        }

        return $this->render("reservation/reservation_validation_form.html.twig", [
            "reservation" => $reservation
        ]);
    }

    #[Route("/{id}/process-validation", name: 'app_reservation_process_validation', methods: ["POST"])]
    public function processValidation(
        #[MapEntity(mapping: ["id" => "id"])] Reservation $reservation,
        #[MapEntity(mapping: ["slug" => "slug"])] Cinema $cinema,
        #[MapQueryParameter()] string $_expiration,
        #[MapQueryParameter()] string $_hash,
        EntityManagerInterface $em
    ): Response {


        $reservation->setValidated(true);
        $em->flush();

        $this->addFlash("success", "Reservation has been successfully validated!");

        return $this->redirectToRoute("app_reservation_show_validation_form", [
            "slug" => $cinema->getSlug(),
            "id" => $reservation->getId(),
            "_hash" => $_hash,
            "_expiration" => $_expiration,
        ]);
    }


    #[Route('/create/showtimes/{showtime_slug}', name: 'app_reservation_create_reservation')]
    public function createReservation(
        Mailer $mailer,
        Request $request,
        ReservationService $reservationService,
        #[MapEntity(mapping: ["slug" => "slug"])] Cinema $cinema,
        #[MapEntity(mapping: ["showtime_slug" => "slug"])] Showtime $showtime
    ) {

        $session = $request->getSession();
        $reservation = $reservationService->createReservation($session, $showtime);

        $mailer->sendReservationReceipt($reservation);

        return $this->redirectToRoute("app_reservation_reserve_showtime", [
            "slug" => $cinema->getSlug(),
            "showtime_slug" => $showtime->getSlug(),

        ]);
    }
}
