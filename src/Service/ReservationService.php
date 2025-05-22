<?php

namespace App\Service;

use App\Entity\Reservation;
use App\Entity\Showtime;
use App\Repository\ReservationSeatRepository;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\RequestStack;

class ReservationService
{

    public function __construct(
        private ReservationSeatRepository $reservationSeatRepository,
        private EntityManagerInterface $em,
        private RequestStack $requestStack,
        private CartService $cartService,
        private Mailer $mailer,
        private LoggerInterface $logger
    ) {}

    public function lockSeats(Showtime $showtime, string $email, string $firstName, ?int $expirationInMinutes = 10): bool
    {
        $reservationSeats = $this->cartService
            ->getReservationSeatsForCheckout($showtime);

        $this->cartService->validateSeats($reservationSeats, $showtime->getId());

        foreach ($reservationSeats as $reservationSeat) {
            $expirationTime = (new \DateTimeImmutable())->modify("+{$expirationInMinutes} minutes");
            $reservationSeat->setStatusLockedExpiresAt($expirationTime);
            $reservationSeat->setStatus('locked');
        }
        $this->em->flush();

        $session = $this->getSession();
        $session->set('email', $email);
        $session->set('firstName', $firstName);

        return true;
    }


    public function createReservation(
        Showtime $showtime,
        string $email,
    ): Reservation {

        $session = $this->getSession();

        $this->logger->info('lemonemo', [
            'email' => $session->get('email'),
            'name' => $session->get('firstName')
        ]);

        $reservationSeats = $this->cartService
            ->getReservationSeatsForCheckout($showtime);

        $reservation = new Reservation();
        $reservation->setEmail($email);
        $reservation->setShowtime($showtime);

        $this->em->persist($reservation);

        $this->em->wrapInTransaction(function ($em)  use ($reservation, $reservationSeats) {
            foreach ($reservationSeats as $reservationSeat) {
                $reservationSeat->setReservation($reservation);
                $reservationSeat->setStatus("reserved");
            }
            $em->flush();
        });

        $this->mailer->sendReservationDetails($reservation);

        $this->cartService->clearCartForShowtimeId($showtime->getId());

        // $session->remove("email");
        // $session->remove('firstName');

        return $reservation;
    }



    private function getSession()
    {
        return $this->requestStack->getSession();
    }
}
