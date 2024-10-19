<?php

namespace App\Scheduler\Handler;

use App\Repository\ReservationSeatRepository;
use App\Scheduler\Message\UnlockReservedSeats;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler()]
class UnlockReservedSeatsHandler {

    public function __construct(
        private ReservationSeatRepository $reservationSeatRepository,
        private EntityManagerInterface $em)
    {
    }

    public function __invoke(UnlockReservedSeats $message)
    {
        $lockedSeats = $this->reservationSeatRepository->findExpiredLockedSeats((new \DateTimeImmutable()));
        
        foreach($lockedSeats as $lockedSeat) {
            $lockedSeat->setStatus("available");
            $lockedSeat->setEmail(null);
            $lockedSeat->setStatusLockedExpiresAt(null);

            $this->em->flush();
        }
        
    
    }
}