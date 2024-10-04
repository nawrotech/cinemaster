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
        private CinemaSeatRepository $cinemaSeatRepository,
        private EntityManagerInterface $em
    ) {}

    // screeningRoom
    private function decreaseNumberOfSeats(
        Cinema $cinema,
        int $rowStart,
        int $rowEnd,
        int $colStart,
        int $colEnd
    ) {
        // screeningRoomSeatRepository
        $inactiveRows = $this->cinemaSeatRepository
            ->findSeatsInGivenRange($cinema, $rowStart, $rowEnd, $colStart, $colEnd);

        foreach ($inactiveRows as $inactiveRow) {
            $inactiveRow->setStatus("inactive");
        }

        $this->em->flush();
    }
    // screeningRoom
    private function increseNumberOfSeats(
        Cinema $cinema,
        int $rowStart,
        int $rowEnd,
        int $colStart,
        int $colEnd
    ) {
        $seats = $this->seatRepository
            ->findSeatsInRange($rowStart, $rowEnd, $colStart, $colEnd);

        foreach ($seats as $seat) {
            // screeningRoomSeatRepository, search by screeningRoom
            $existingSeat = $this->cinemaSeatRepository->findOneBy([
                'cinema' => $cinema,
                'seat' => $seat
            ]);
            if ($existingSeat) {
                $existingSeat->setStatus('active');
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
        $this->em->beginTransaction();

        try {

            $this->storeChanges($cinema);

            $this->adjustSeats($cinema);

            $this->em->flush();
            $this->em->commit();
        } catch (\Exception $e) {
            $this->em->rollback();
            throw $e;
        }
    }

    private function adjustSeats(Cinema $cinema)
    {
        // also screeningRoomSeatRepository
        $lastSeat = $this->cinemaSeatRepository->findLastSeat($cinema);

        // lol screening room has that too on it
        if ($cinema->getRowsMax() > $lastSeat["row"]) {
            $this->increseNumberOfSeats(
                $cinema,
                $lastSeat["row"],
                $cinema->getRowsMax(),
                1,
                $lastSeat["col"]
            );
        }
        // lol screening room has that too on it
        if ($cinema->getRowsMax() < $lastSeat["row"]) {
            $rowStart = $cinema->getRowsMax() + 1;

            $this->decreaseNumberOfSeats(
                $cinema,
                $rowStart,
                $lastSeat["row"],
                1,
                $lastSeat["col"]
            );
        }

        $lastSeat = $this->cinemaSeatRepository->findLastSeat($cinema);
        // lol screening room has that too on it
        if ($cinema->getSeatsPerRowMax() > $lastSeat["col"]) {

            $this->increseNumberOfSeats(
                $cinema,
                1,
                $lastSeat["row"],
                $lastSeat["col"],
                $cinema->getSeatsPerRowMax()
            );
        }
        // lol screening room has that too on it
        if ($cinema->getSeatsPerRowMax() < $lastSeat["col"]) {
            $colStart = $cinema->getSeatsPerRowMax() + 1;

            $this->decreaseNumberOfSeats(
                $cinema,
                1,
                $lastSeat["row"],
                $colStart,
                $lastSeat["col"]
            );
        }
    }

    private function storeChanges(Cinema $cinema)
    {
        // right now it should be cinema and screening room
        // both of them are seats holders.

        $unitOfWork = $this->em->getUnitOfWork();
        $unitOfWork->computeChangeSets();
        $cinemaChange = $unitOfWork->getEntityChangeSet($cinema);


        $cinemaHistory = new CinemaHistory();
        $cinemaHistory->setCinema($cinema);
        $cinemaHistory->setChanges($cinemaChange);
        $this->em->persist($cinemaHistory);
    }
}
