<?php

namespace App\Service;

use App\Entity\PriceTier;
use App\Entity\ScreeningRoom;
use App\Entity\ScreeningRoomSeat;
use App\Exception\InvalidRowsAndSeatsStructureException;
use App\Repository\SeatRepository;
use Doctrine\ORM\EntityManagerInterface;

class SeatService
{

    public function __construct(
        private SeatRepository $seatRepository,
        private EntityManagerInterface $em
    ) {}

    private function calculateMaxRowAndSeat(array $rowsAndSeats): array
    {
        if (empty($rowsAndSeats)) {
            throw new \InvalidArgumentException('Seats per row array cannot be empty.');
        }

        if ($rowsAndSeats !== array_combine(range(1, count($rowsAndSeats)), array_values($rowsAndSeats))) {
            throw new InvalidRowsAndSeatsStructureException('Seats per row array must be 1-based and sequential.');
        }

        $maxRow = array_key_last($rowsAndSeats);
        $maxSeatsInRow = max($rowsAndSeats);

        return [$maxRow, $maxSeatsInRow];
    }

    public function groupSeatsByRow(array $rowsAndSeats)
    {

        [$maxRow, $maxSeatsInRow]  = $this->calculateMaxRowAndSeat($rowsAndSeats);

        $seats = $this->seatRepository->findSeatsUpToMax($maxRow, $maxSeatsInRow);

        $seatsByRow = [];
        foreach ($seats as $seat) {
            $seatsByRow[$seat->getRowNum()][] = $seat;
        }

        return $seatsByRow;
    }



    public function assignSeatsToScreeningRoom(ScreeningRoom $screeningRoom, array $rowsAndSeats, PriceTier $priceTier)
    {

        $this->em->wrapInTransaction(function ($em) use ($rowsAndSeats, $screeningRoom, $priceTier) {

            $em->persist($screeningRoom);
            $em->flush();

            $seatsByRow = $this->groupSeatsByRow($rowsAndSeats);

            $batchSize = 100;

            foreach ($rowsAndSeats as $row => $lastSeatInRow) {
                $seatsForRow = array_slice($seatsByRow[$row] ?? [], 0, $lastSeatInRow);

                foreach ($seatsForRow as $seat) {
                    $screeningRoomSeat = new ScreeningRoomSeat();
                    $screeningRoomSeat->setScreeningRoom($screeningRoom);
                    $screeningRoomSeat->setSeat($seat);
                    $screeningRoomSeat->setPriceTier($priceTier);
                    $em->persist($screeningRoomSeat);
                    if ((($seat->getRowNum() * $seat->getSeatNumInRow()) % $batchSize) === 0) {
                        $em->flush();
                        foreach ($this->em->getUnitOfWork()->getIdentityMap()['App\Entity\ScreeningRoomSeat'] ?? [] as $entity) {
                            $this->em->detach($entity);
                        }
                    }
                }
            }

            $em->flush();
            $em->clear();
        });
    }
}
