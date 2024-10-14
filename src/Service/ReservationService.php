<?php

namespace App\Service;

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
}