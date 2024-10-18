<?php

namespace App\Service;

use App\Entity\Cinema;
use App\Entity\CinemaHistory;
use App\Entity\CinemaSeat;
use App\Repository\CinemaSeatRepository;
use App\Repository\SeatRepository;
use Doctrine\ORM\EntityManagerInterface;

class CinemaChangeService
{

    public function __construct(
        private SeatRepository $seatRepository,
        // common interface for srsR and csR
        private CinemaSeatRepository $cinemaSeatRepository,
        private EntityManagerInterface $em
    ) {}

    // screeningRoom
    private function decreaseNumberOfSeats(
        Cinema $cinema,
        int $rowStart,
        int $rowEnd,
        int $seatInRowStart,
        int $seatInRowEnd,
        bool $excludeFirstRow = false,
        bool $excludeFirstSeatInRow = false
    ) {
     
        $invisibleSeats = $this->cinemaSeatRepository
            ->findSeatsInRange($cinema,
                $rowStart, 
                $rowEnd, 
                $seatInRowStart, 
                $seatInRowEnd,
                $excludeFirstRow,
                $excludeFirstSeatInRow
            );

        foreach ($invisibleSeats as $invisibleSeat) {
            $invisibleSeat->setVisible(false);
        }

        $this->em->flush();
    }
    // screeningRoom 
    private function increaseNumberOfSeats(
        Cinema $cinema,
        int $rowStart, 
        int $rowEnd, 
        int $seatInRowStart, 
        int $seatInRowEnd
    ) {
        $seats = $this->seatRepository
            ->findSeatsInRange($rowStart, $rowEnd, $seatInRowStart, $seatInRowEnd);

        foreach ($seats as $seat) {
            $existingSeat = $this->cinemaSeatRepository->findOneBy([
                "cinema" => $cinema,
                "seat" => $seat
            ]);
            if ($existingSeat) {
                $existingSeat->setVisible(true);
            } else {
                // screeningRoom Seat
                $cinemaSeat = new CinemaSeat();
                $cinemaSeat->setCinema($cinema);
                $cinemaSeat->setSeat($seat);

                $this->em->persist($cinemaSeat);
            }
        }
        $this->em->flush();
    }

    public function handleSeatsChange(Cinema $cinema)
    {
        $this->em->wrapInTransaction(function ($em) use($cinema) {
            $this->storeChanges($cinema);

            $this->adjustSeats($cinema);

            $em->flush();
        });

    }

    private function adjustSeats(Cinema $cinema)
    {
       
        $lastSeat = $this->cinemaSeatRepository->findLastSeat($cinema);

        if ($cinema->getMaxRows() > $lastSeat["lastRowNum"]) {
            $this->increaseNumberOfSeats(
                $cinema,
                $lastSeat["lastRowNum"],
                $cinema->getMaxRows(),
                1,
                $lastSeat["lastSeatNumInRow"]
            );
        }
       
        if ($cinema->getMaxRows() < $lastSeat["lastRowNum"]) {
            $this->decreaseNumberOfSeats(
                $cinema,
                $cinema->getMaxRows(), 
                $lastSeat["lastRowNum"],
                1,
                $lastSeat["lastSeatNumInRow"],
                excludeFirstRow: true
                
            );
        }

        $lastSeat = $this->cinemaSeatRepository->findLastSeat($cinema);
       
        if ($cinema->getMaxSeatsPerRow() > $lastSeat["lastSeatNumInRow"]) {
            $this->increaseNumberOfSeats(
                $cinema,
                1,
                $lastSeat["lastRowNum"],
                $lastSeat["lastSeatNumInRow"],
                $cinema->getMaxSeatsPerRow()
            );
        }
    
        if ($cinema->getMaxSeatsPerRow() < $lastSeat["lastSeatNumInRow"]) {

            $this->decreaseNumberOfSeats(
                $cinema,
                1,
                $lastSeat["lastRowNum"],
                $cinema->getMaxSeatsPerRow(),
                $lastSeat["lastSeatNumInRow"],
                excludeFirstSeatInRow: true
            );
        }
    }

    private function storeChanges(Cinema $cinema)
    {
        $unitOfWork = $this->em->getUnitOfWork();
        $unitOfWork->computeChangeSets();
        $cinemaChange = $unitOfWork->getEntityChangeSet($cinema);

        $cinemaHistory = new CinemaHistory();
        $cinemaHistory->setCinema($cinema);
        $cinemaHistory->setChanges($cinemaChange);
        $this->em->persist($cinemaHistory);
    }
}
