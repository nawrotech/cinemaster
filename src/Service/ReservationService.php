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

    public function lockSeats(SessionInterface $session, string $email, ?int $expirationInMinutes = 5) {
        $this->em->beginTransaction();
       try {
            foreach($session->get("cart") as $seatId) {
                $reservationSeat = $this->reservationSeatRepository->find($seatId);
                $reservationSeat->setStatus("locked");

                $expirationTime = (new \DateTimeImmutable())->modify("+{$expirationInMinutes} minutes");
                $reservationSeat->setStatusLockedExpiresAt($expirationTime);
                $reservationSeat->setEmail($email);

            }
            $this->em->flush();
            $this->em->commit();

        } catch(\Exception $e) {
                $this->em->rollback();
                throw $e;
        }
        
    }

    public function createReservation(SessionInterface $session) {
        $this->em->beginTransaction();
        try {
            foreach($session->get("cart") as $seatId) {
                $reservationSeat = $this->reservationSeatRepository->find($seatId);
                
                $reservation = new Reservation();
                $reservation->setEmail($reservationSeat->getEmail());
                $reservation->setShowtime($reservationSeat->getShowtime());
                $reservationSeat->setReservation($reservation);
    
                $reservationSeat->setStatus("reserved");
                $reservationSeat->setStatusLockedExpiresAt(null);
    
                $this->em->persist($reservation);
            }

            $this->em->flush();
            $this->em->commit();

        } catch (\Exception $e) {
            $this->em->rollback();
            throw $e;
        }
 
        $this->em->flush();
        $session->clear("cart");
    }
}