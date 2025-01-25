<?php

namespace App\Service;

use App\Entity\ScreeningRoom;
use App\Enum\ScreeningRoomSeatType;
use App\Repository\ScreeningRoomSeatRepository;
use Doctrine\ORM\EntityManagerInterface;

class ScreeningRoomSeatService {

    public function __construct(
        private ScreeningRoomSeatRepository $screeningRoomSeatRepository, 
        private EntityManagerInterface $em)
    {
    }


    /** 
     * @return ScreeningRoomSeat[]
     */
    public function groupSeatsForLayout(ScreeningRoom $screeningRoom): array 
    {
        $screeningRoomSeats = $this->screeningRoomSeatRepository->findSeatsByScreeningRoom($screeningRoom);
        foreach ($screeningRoomSeats as $screeningRoomSeat) {
            $rowNum = $screeningRoomSeat->getSeat()->getRowNum();
            $groupedSeats[$rowNum][] = $screeningRoomSeat;
        }

        return $groupedSeats;
    }

    public function updateSeatTypeForRow(
        ScreeningRoom $screeningRoom,
        int $row,
        int $firstSeatInRow,
        int $lastSeatInRow,
        ScreeningRoomSeatType $seatType
    ): void {
        $seatsInRow = $this->screeningRoomSeatRepository->findSeatsInRange(
            $screeningRoom,
            $row,
            $row,
            $firstSeatInRow,
            $lastSeatInRow
        );

        foreach ($seatsInRow as $screeningRoomSeat) {
            $screeningRoomSeat->setType($seatType);
        }

        $this->em->flush();
    }
 
}