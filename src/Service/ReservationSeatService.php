<?php

namespace App\Service;

use App\Entity\ReservationSeat;
use App\Entity\Showtime;
use App\Repository\ReservationSeatRepository;

class ReservationSeatService
{
    public function __construct(private ReservationSeatRepository $reservationSeatRepository) {}

    /** 
     * @return ReservationSeat[]
     */
    public function groupSeatsForLayout(Showtime $showtime): array
    {
        $screeningRoomSeats = $this->reservationSeatRepository->findSeatsByShowtime($showtime);

        // TODO change first layer seat to specific getScreeningRoomSeat
        foreach ($screeningRoomSeats as $screeningRoomSeat) {
            $rowNum = $screeningRoomSeat->getSeat()->getSeat()->getRowNum();
            $groupedSeats[$rowNum][] = $screeningRoomSeat;
        }

        return $groupedSeats;
    }
}
