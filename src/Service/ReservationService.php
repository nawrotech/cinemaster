<?php

namespace App\Service;

use App\Entity\Reservation;
use App\Entity\Showtime;
use App\Repository\ReservationSeatRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class ReservationService {

    public function __construct(
        private ReservationSeatRepository $reservationSeatRepository,
        private EntityManagerInterface $em)
    {
    }

    public function lockSeats(SessionInterface $session, string $email, ?int $expirationInMinutes = 10) {
        
            foreach($session->get("cart", []) as $seatId) {
                $reservationSeat = $this->reservationSeatRepository->find($seatId);

                $expirationTime = (new \DateTimeImmutable())->modify("+{$expirationInMinutes} minutes");
                $reservationSeat->setStatusLockedExpiresAt($expirationTime);
                $session->set("email", $email);

            }
            $this->em->flush();            
    
    }

    public function createReservation(SessionInterface $session, Showtime $showtime): Reservation {
        $email = $session->get("email");
        $cart = $session->get("cart");

        $reservation = new Reservation();
        $reservation->setEmail($email);
        $reservation->setShowtime($showtime);

        $this->em->persist($reservation);

        $this->em->wrapInTransaction(function ($em)  use($reservation, $cart){
            foreach($cart as $seatId) {
                $reservationSeat = $this->reservationSeatRepository->find($seatId);
                
                if (!$reservationSeat) {
                    throw new \Exception("Seat not found or already locked.");
                }

                $reservationSeat->setReservation($reservation);
                $reservationSeat->setStatus("reserved");
    
            }
            $em->flush();
  
        });

        $session->remove("cart");
        $session->remove("email");
        return $reservation;

    }
}