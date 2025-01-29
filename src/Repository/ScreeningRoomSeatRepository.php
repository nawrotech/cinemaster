<?php

namespace App\Repository;

use App\Entity\ScreeningRoom;
use App\Entity\ScreeningRoomSeat;
use App\Entity\Showtime;
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

    /**
     * @return int[]
     */
    public function findRows(ScreeningRoom $screeningRoom): array
    {
        $result = $this->createQueryBuilder('srs')
            ->select("s.rowNum")
            ->distinct()
            ->innerJoin("srs.screeningRoom", "sr")
            ->innerJoin("srs.seat", "s")
            ->andWhere("sr = :screeningRoom")
            ->setParameter("screeningRoom", $screeningRoom)
            ->addOrderBy("s.rowNum", "ASC")
            ->getQuery()
            ->getResult();
     
        return array_map("intval", array_column($result, "rowNum"));
    }

    /**
    * @return ScreeningRoomSeat[]
    */
    public function findSeatsInRow(
        ScreeningRoom $screeningRoom,
        int $rowNum, 
        ?Showtime $showtime = null): array
    {
        return $this->createQueryBuilder('srs')
            ->innerJoin("srs.screeningRoom", "sr")
            ->innerJoin("srs.seat", "s")
            ->andWhere("sr = :screeningRoom AND s.rowNum = :rowNum")
            ->orderBy("s.rowNum", "ASC")
            ->orderBy("s.seatNumInRow", "ASC")
            ->setParameter("rowNum", $rowNum)
            ->setParameter("screeningRoom", $screeningRoom)
            ->getQuery()
            ->getResult();         
        ;
    }

    // SUPER USELESS
    // public function findBySeatId(int $roomId, int $seatId)
    // {

    //     return $this->createQueryBuilder('srs')
    //         ->innerJoin("srs.screeningRoom", "sr")
    //         ->andWhere("srs.id = :seatId")
    //         ->andWhere("sr.id = :roomId")
    //         ->setParameter("seatId", $seatId)
    //         ->setParameter("roomId", $roomId)
    //         ->getQuery()
    //         ->getOneOrNullResult();
    // }

    public function findSeatsByScreeningRoom(ScreeningRoom $screeningRoom)  {
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


    // public function findNumOfRowsForRoom(int $roomId): ?ScreeningRoomSeat
    // {
    //     return $this->createQueryBuilder('s')
    //         ->select("COUNT(DISTINCT )")
    //         ->where('s. = :roomId')
    //         ->setParameter('val', $value)
    //         ->getQuery()
    //         ->getOneOrNullResult()
    //     ;
    // }
}
