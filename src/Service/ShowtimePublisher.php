<?php

namespace App\Service;

use App\Entity\Showtime;
use App\Repository\ReservationSeatRepository;
use Doctrine\ORM\EntityManagerInterface;

class ShowtimePublisher
{
    public function __construct(
        private EntityManagerInterface $em,
        private ReservationSeatRepository $reservationSeatRepository,
        private ShowtimeService $showtimeService
    ) {}

    public function unpublish(Showtime $showtime): bool
    {
        if ($showtime->getReservations()->count() > 0) {
            return false;
        }
        $showtime->setPublished(false);
        $this->em->flush();

        return true;
    }

    public function publish(Showtime $showtime): void
    {
        if ($this->reservationSeatRepository->count(['showtime' => $showtime]) === 0) {
            $this->showtimeService->publishShowtime($showtime);
        } else {
            $showtime->setPublished(true);
            $this->em->flush();
        }
    }
}
