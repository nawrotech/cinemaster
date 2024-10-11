<?php

namespace App\Service;

use App\Contracts\SeatsGridInterface;
use App\Entity\ScreeningRoom;
use App\Entity\Showtime;

class SeatsService {

    public function createGrid(
        ScreeningRoom $screeningRoom,
        SeatsGridInterface $repository,
        ?Showtime $showtime = null
        ): array {
        $roomRows = $repository
            ->findRows($screeningRoom);
        
        $seatsInRow = [];
        foreach ($roomRows as $roomRow) {
            $seatsInRow[$roomRow] = $repository
                ->findSeatsInRow($screeningRoom, $roomRow, $showtime);
        }

        return [
            "roomRows" => $roomRows, 
            "seatsInRow" => $seatsInRow
        ];
    }






}