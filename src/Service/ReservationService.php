<?php

namespace App\Service;

use App\Entity\Reservation;
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
        
        $this->em->wrapInTransaction(function ($em)  use($session, $email, $expirationInMinutes){
            foreach($session->get("cart") as $seatId) {
                $reservationSeat = $this->reservationSeatRepository->find($seatId);

                $expirationTime = (new \DateTimeImmutable())->modify("+{$expirationInMinutes} minutes");
                $reservationSeat->setStatusLockedExpiresAt($expirationTime);
                $session->set("email", $email);

            }
            $em->flush();            
        });
    
    }

    public function createReservation(SessionInterface $session): Reservation {
        
        $reservation = new Reservation();

        $this->em->wrapInTransaction(function ($em)  use($session, $reservation){
            foreach($session->get("cart") as $seatId) {
                $reservationSeat = $this->reservationSeatRepository->find($seatId);
                
                if (!$reservationSeat) {
                    throw new \Exception("Seat not found or already locked.");
                }

                $reservation->setEmail($session->get("email"));
                $reservation->setShowtime($reservationSeat->getShowtime());
                $reservationSeat->setReservation($reservation);
    
                $reservationSeat->setStatus("reserved");
    
                $em->persist($reservation);
                $em->flush();

            }
            $session->clear("cart");
            $session->clear("email");
        });
     
    
        return $reservation;

    }
}