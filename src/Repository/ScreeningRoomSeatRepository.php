<?php

namespace App\Repository;

use App\Entity\ScreeningRoom;
use App\Entity\ScreeningRoomSeat;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ScreeningRoomSeat>
 */
class ScreeningRoomSeatRepository extends ServiceEntityRepository
{
    public function __construct(
        ManagerRegistry $registry,
        private EntityManagerInterface $em
    ) {
        parent::__construct($registry, ScreeningRoomSeat::class);
    }

    //    /**
    //     * @return ScreeningRoomSeat[] Returns an array of ScreeningRoomSeat objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('s')
    //            ->andWhere('s.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('s.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?ScreeningRoomSeat
    //    {
    //        return $this->createQueryBuilder('s')
    //            ->andWhere('s.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }

    public function findNumOfRowsForRoom(ScreeningRoom $screeningRoom)
    {

        $result = $this->createQueryBuilder('srs')
            ->select("DISTINCT s.rowNum")
            ->innerJoin("srs.screeningRoom", "sr")
            ->innerJoin("srs.seat", "cs")
            ->innerJoin("cs.seat", "s")
            ->andWhere("sr = :screeningRoom")
            ->setParameter("screeningRoom", $screeningRoom)
            ->getQuery()
            ->getResult();
        // return $result;
        return array_map("intval", array_column($result, "rowNum"));
    }

    public function findSeatsInRow(ScreeningRoom $screeningRoom, int $rowNum)
    {

        return $this->createQueryBuilder('srs')
            ->innerJoin("srs.screeningRoom", "sr")
            ->innerJoin("srs.seat", "cs")
            ->innerJoin("cs.seat", "s")
            ->andWhere("sr = :screeningRoom AND s.rowNum = :rowNum")
            ->setParameter("rowNum", $rowNum)
            ->setParameter("screeningRoom", $screeningRoom)
            ->getQuery()
            ->getResult()
        ;
    }

    public function findBySeatId(int $roomId, int $seatId)
    {

        return $this->createQueryBuilder('srs')
            ->innerJoin("srs.screeningRoom", "sr")
            ->innerJoin("srs.seat", "cs")
            ->innerJoin("cs.seat", "s")
            ->andWhere("s.id = :seatId")
            ->andWhere("sr.id = :roomId")
            ->setParameter("seatId", $seatId)
            ->setParameter("roomId", $roomId)
            ->getQuery()
            ->getOneOrNullResult();
    }

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
