<?php

namespace App\Repository;

use App\Entity\ScreeningRoom;
use App\Entity\ScreeningRoomSeat;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ScreeningRoomSeat>
 */
class ScreeningRoomSeatRepository extends ServiceEntityRepository
{
    public function __construct(
        ManagerRegistry $registry,
    ) {
        parent::__construct($registry, ScreeningRoomSeat::class);
    }

    public function findSeatsByScreeningRoom(ScreeningRoom $screeningRoom)
    {
        return $this->createQueryBuilder('srs')
            ->addSelect("s")
            ->innerJoin("srs.seat", "s")
            ->andWhere("srs.screeningRoom = :screeningRoom")
            ->setParameter("screeningRoom", $screeningRoom)
            ->addOrderBy("s.rowNum", "ASC")
            ->addOrderBy("s.seatNumInRow", "ASC")
            ->getQuery()
            ->getResult();
    }

    public function findSeatsInRange(
        ScreeningRoom $screeningRoom,
        int $rowNumStart,
        int $rowNumEnd,
        int $seatNumInRowStart,
        int $seatNumInRowEnd
    ) {

        return $this->createQueryBuilder('srs')
            ->innerJoin("srs.screeningRoom", "sr")
            ->innerJoin("srs.seat", "s")
            ->andWhere("s.rowNum BETWEEN :rowNumStart AND :rowNumEnd")
            ->andWhere("s.seatNumInRow BETWEEN :seatNumInRowStart AND :seatNumInRowEnd")
            ->andWhere("srs.screeningRoom = :screeningRoom")
            ->setParameter("screeningRoom", $screeningRoom)
            ->setParameter("rowNumStart", $rowNumStart)
            ->setParameter("rowNumEnd", $rowNumEnd)
            ->setParameter("seatNumInRowStart", $seatNumInRowStart)
            ->setParameter("seatNumInRowEnd", $seatNumInRowEnd)
            ->addOrderBy("s.rowNum", "ASC")
            ->addOrderBy("s.seatNumInRow", "ASC")
            ->getQuery()
            ->getResult();
    }



}
