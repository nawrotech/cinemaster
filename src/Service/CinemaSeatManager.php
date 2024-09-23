<?php

namespace App\Service;

use App\Entity\Cinema;
use App\Entity\CinemaSeat;
use App\Repository\CinemaRepository;
use App\Repository\CinemaSeatRepository;
use App\Repository\SeatRepository;
use Doctrine\ORM\EntityManagerInterface;

class CinemaSeatManager
{

    public function __construct(
        private SeatRepository $seatRepository,
        private CinemaSeatRepository $cinemaSeatRepository,
        private EntityManagerInterface $em

    ) {}

    public function decreaseNumberOfSeats(
        Cinema $cinema,
        int $rowStart,
        int $rowEnd,
        int $colStart,
        int $colEnd
    ) {


        $inactiveRows = $this->cinemaSeatRepository
            ->findSeatsInGivenRange($cinema, $rowStart, $rowEnd, $colStart, $colEnd);

        foreach ($inactiveRows as $inactiveRow) {
            $inactiveRow->setStatus("inactive");
        }

        $this->em->flush();
    }

    public function increseNumberOfSeats(
        Cinema $cinema,
        int $rowStart,
        int $rowEnd,
        int $colStart,
        int $colEnd
    ) {
        // $startingRow = $currentRow + 1;
        $seats = $this->seatRepository
            ->findSeatRowsInRange($rowStart, $rowEnd, $colStart, $colEnd);

        foreach ($seats as $seat) {
            $existingSeat = $this->cinemaSeatRepository->findOneBy([
                'cinema' => $cinema,
                'seat' => $seat
            ]);
            if ($existingSeat) {
                $existingSeat->setStatus('active');
            } else {
                $cinemaSeat = new CinemaSeat();
                $cinemaSeat->setCinema($cinema);
                $cinemaSeat->setSeat($seat);

                $this->em->persist($cinemaSeat);
            }
        }
        $this->em->flush();
    }
}
