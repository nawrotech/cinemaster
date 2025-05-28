<?php

namespace App\Controller;

use App\Entity\Cinema;
use App\Entity\Reservation;
use App\Entity\Showtime;
use App\Form\ReservationType;
use App\Repository\ReservationSeatRepository;
use App\Service\CartService;
use App\Service\PriceTierExtractorService;
use App\Service\ReservationSeatService;
use App\Service\ReservationService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\ExpressionLanguage\Expression;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\UriSigner;
use Symfony\Component\HttpKernel\Attribute\MapQueryParameter;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsCsrfTokenValid;

#[Route("/cinemas/{slug}/reservations")]
class ReservationController extends AbstractController
{
    #[Route('/{showtime_slug}', name: 'app_reservation_reserve_showtime',  methods: ['GET', 'POST'])]
    public function reserveShowtime(
        ReservationService $reservationService,
        ReservationSeatService $reservationSeatService,
        Request $request,
        #[MapEntity(mapping: ["slug" => "slug"])] Cinema $cinema,
        #[MapEntity(mapping: ["showtime_slug" => "slug"])] Showtime $showtime,
        PriceTierExtractorService $priceTierExtractorService,
        CartService $cartService,
    ): Response {

        if (!$showtime->isPublished()) {
            $this->addFlash('danger', 'An error occurred, please try again later!');

            return $this->redirectToRoute('app_main_cinema_showtimes', [
                'slug' => $cinema->getSlug()
            ]);
        }

        $priceTiers = $priceTierExtractorService->getShowtimePriceTiers($showtime);
        $groupedReservationSeats = $reservationSeatService->groupSeatsForLayout($showtime);

        $session = $request->getSession();
        $cart = $session->get('cart');

        $selectedSeats = $cart[$showtime->getId()] ?? [];

        $form = $this->createForm(ReservationType::class, [
            'email' => $session->get('email'),
            'firstName' => $session->get('firstName')
        ], [
            "selectedSeats" => $selectedSeats
        ]);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $email = $form->get('email')->getData();
            $firstName = $form?->get('firstName')?->getData();

            try {
                $success = $reservationService->lockSeats($showtime, $email, $firstName);

                if (!$success) {
                    $cartService->clearCartForShowtimeId($showtime->getId());

                    $this->addFlash('danger', 'Problem with reservation occurred, please try again later.');
                    return $this->redirectToRoute('app_reservation_reserve_showtime', [
                        'slug' => $cinema->getSlug(),
                        'showtime_slug' => $showtime->getSlug()
                    ]);
                }

                return $this->redirectToRoute("app_order_checkout", [
                    "id" => $showtime->getId()
                ]);
            } catch (\Exception $e) {
                $cartService->clearCartForShowtimeId($showtime->getId());

                $this->addFlash('danger', 'An error occurred, please try again later.');
                return $this->redirectToRoute('app_reservation_reserve_showtime', [
                    'slug' => $cinema->getSlug(),
                    'showtime_slug' => $showtime->getSlug()
                ]);
            }
        }

        return $this->render('reservation/index.html.twig', [
            "groupedReservationSeats" => $groupedReservationSeats,
            "showtime" => $showtime,
            "form" => $form,
            "cinema" => $cinema,
            'priceTiers' => $priceTiers
        ]);
    }

    #[IsCsrfTokenValid(new Expression('"select-seat-" ~ request.get("reservation_seat_id")'), tokenKey: 'token')]
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

        $selectedSeat  = $reservationSeatRepository->find($reservationSeatId);

        $now = new \DateTimeImmutable();
        $isNotAvailable = $selectedSeat->getStatus() !== "available" ||
            ($selectedSeat->getStatusLockedExpiresAt() !== null &&
                $selectedSeat->getStatusLockedExpiresAt() > $now);
        if ($isNotAvailable) {
            return $this->redirectToRoute("app_reservation_reserve_showtime", [
                "slug" => $cinema->getSlug(),
                "showtime_slug" => $showtime->getSlug()
            ]);
        }

        $showtimeId = $showtime->getId();
        if ($request->get("reserve")) {
            $cartService->addSeat($reservationSeatId, $showtimeId);
        }

        if ($request->get("cancel")) {
            $cartService->removeSeat($reservationSeatId, $showtimeId);
        }

        return $this->redirectToRoute("app_reservation_reserve_showtime", [
            "slug" => $cinema->getSlug(),
            "showtime_slug" => $showtime->getSlug()
        ]);
    }

    #[Route("/{id}/validation-form", name: 'app_reservation_ticket_validation_form')]
    public function ticketValidationForm(
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

    #[Route("/{id}/process-validation", name: 'app_reservation_process_ticket_validation', methods: ["POST"])]
    public function processTicketValidation(
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
}
