<?php

namespace App\Service;

use App\Contracts\SeatsGridInterface;
use App\Entity\ScreeningRoom;
use App\Entity\ScreeningRoomSeat;
use App\Entity\Showtime;
use App\Repository\SeatRepository;
use Doctrine\ORM\EntityManagerInterface;

class SeatsService {

    public function __construct(
        private SeatRepository $seatRepository, 
        private EntityManagerInterface $em)
    {
    }

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

    public function calculateMaxRowAndSeat(array $rowsAndSeats): array {
        if (empty($rowsAndSeats)) {
            throw new \InvalidArgumentException('Seats per row array cannot be empty.');
        }

        $maxRow = array_key_last($rowsAndSeats);
        $maxSeatsInRow = max($rowsAndSeats);

        return [$maxRow, $maxSeatsInRow];

    }

    public function groupSeatsByRow(array $rowsAndSeats) {

        [$maxRow, $maxSeatsInRow]  = $this->calculateMaxRowAndSeat($rowsAndSeats);

        $seats = $this->seatRepository->findSeatsUpToMax($maxRow, $maxSeatsInRow);

        $seatsByRow = [];
        foreach ($seats as $seat) {
            $seatsByRow[$seat->getRowNum()][] = $seat;
        }

        return $seatsByRow;
    }



    public function assignSeatsToScreeningRoom(ScreeningRoom $screeningRoom, array $rowsAndSeats) {

        $this->em->wrapInTransaction(function($em) use($rowsAndSeats, $screeningRoom) {

            $seatsByRow = $this->groupSeatsByRow($rowsAndSeats);

            foreach ($rowsAndSeats as $row => $lastSeatInRow) {
                $seatsForRow = array_slice($seatsByRow[$row] ?? [], 0, $lastSeatInRow);
                
                foreach ($seatsForRow as $seat) {
                    $screeningRoomSeat = new ScreeningRoomSeat();
                    $screeningRoomSeat->setScreeningRoom($screeningRoom);
                    $screeningRoomSeat->setSeat($seat);
                    $em->persist($screeningRoomSeat);
                }
            }

            $em->persist($screeningRoom);
            $em->flush();
        });
        
    }







}