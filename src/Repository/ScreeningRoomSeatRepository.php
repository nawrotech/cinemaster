<?php

namespace App\Repository;

use App\Contracts\SeatsGridInterface;
use App\Entity\ScreeningRoom;
use App\Entity\ScreeningRoomSeat;
use App\Entity\Showtime;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ScreeningRoomSeat>
 */
class ScreeningRoomSeatRepository extends ServiceEntityRepository implements SeatsGridInterface
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
            ->select("DISTINCT s.rowNum")
            ->innerJoin("srs.screeningRoom", "sr")
            ->innerJoin("srs.seat", "cs")
            ->innerJoin("cs.seat", "s")
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
            ->innerJoin("srs.seat", "cs")
            ->innerJoin("cs.seat", "s")
            ->andWhere("sr = :screeningRoom AND s.rowNum = :rowNum")
            ->orderBy("s.rowNum", "ASC")
            ->orderBy("s.colNum", "ASC")
            ->setParameter("rowNum", $rowNum)
            ->setParameter("screeningRoom", $screeningRoom)
            ->getQuery()->getResult();         
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

    public function findSeatsInRange(
        ScreeningRoom $screeningRoom,
        int $rowStart,
        int $rowEnd,
        int $colStart,
        int $colEnd
    ) {

        return $this->createQueryBuilder('srs')
            ->innerJoin("srs.screeningRoom", "sr")
            ->innerJoin("srs.seat", "cs")
            ->innerJoin("cs.seat", "s")
            ->andWhere("s.rowNum BETWEEN :rowStart AND :rowEnd")
            ->andWhere("s.colNum BETWEEN :colStart AND :colEnd")
            ->andWhere("sr = :screeningRoom")
            ->setParameter("screeningRoom", $screeningRoom)
            ->setParameter("rowStart", $rowStart)
            ->setParameter("rowEnd", $rowEnd)
            ->setParameter("colStart", $colStart)
            ->setParameter("colEnd", $colEnd)
            ->addOrderBy("s.rowNum", "ASC")
            ->addOrderBy("s.colNum", "ASC")
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
