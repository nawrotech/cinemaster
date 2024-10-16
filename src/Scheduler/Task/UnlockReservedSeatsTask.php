<?php

namespace App\Scheduler\Task;

use App\Repository\ReservationSeatRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Scheduler\Attribute\AsPeriodicTask;

#[AsPeriodicTask(frequency: 60)]
class UnlockReservedSeatsTask {

    public function __construct(
        private ReservationSeatRepository $reservationSeatRepository,
        private EntityManagerInterface $em)
    {
    }

    public function __invoke()
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