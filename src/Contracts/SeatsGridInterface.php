<?php

namespace App\Contracts;

use App\Entity\ScreeningRoom;
use App\Entity\Showtime;

interface SeatsGridInterface {

    /**
     * @return int[]
     */
    public function findRows(ScreeningRoom $screeningRoom): array;

    
    /**
     * @return ReservationSeat[]|ScreeningRoomSeat[] 
     */
    public function findSeatsInRow(
        ScreeningRoom $screeningRoom, int $rowNum, ?Showtime $showtime = null):  array;

 
}