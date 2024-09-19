<?php

namespace App\Repository;

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

    public function findNumOfRowsForRoom(int $roomId)
    {
        $dql = "SELECT DISTINCT s.rowNum 
                    FROM App\Entity\ScreeningRoomSeat srs
                    JOIN srs.seat s
                    JOIN srs.ScreeningRoom sr
                    WHERE sr.id = :roomId";

        $query = $this->em->createQuery($dql);
        $query->setParameter("roomId", $roomId);
        return array_column($query->getResult(), 'rowNum');
    }

    public function findSeatsInRow(int $roomId, int $rowNum)
    {
        $dql = "SELECT srs 
            FROM App\Entity\ScreeningRoomSeat srs
            JOIN srs.seat s
            JOIN srs.ScreeningRoom sr
            WHERE sr.id = :roomId
            AND s.rowNum = :rowNum";

        $query = $this->em->createQuery($dql);
        $query->setParameter("roomId", $roomId);
        $query->setParameter("rowNum", $rowNum);
        return $query->getResult();
    }

    public function findBySeatId(int $roomId, int $seatId)
    {
        $dql = "SELECT srs 
            FROM App\Entity\ScreeningRoomSeat srs
            JOIN srs.seat s
            JOIN srs.ScreeningRoom sr
            WHERE s.id = :seatId
                AND sr.id = :roomId";

        $query = $this->em->createQuery($dql);
        $query->setParameter("seatId", $seatId);
        $query->setParameter("roomId", $roomId);
        return $query->getOneOrNullResult();
    }

    public function findSeatsRangeInRow(int $roomId, int $rowNum, int $colStart, int $colEnd)
    {
        $dql = "SELECT srs 
            FROM App\Entity\ScreeningRoomSeat srs
            JOIN srs.seat s
            JOIN srs.ScreeningRoom sr
            WHERE s.rowNum = :rowNum
                AND s.colNum BETWEEN :colStart AND :colEnd
                AND sr.id = :roomId";

        $query = $this->em->createQuery($dql);
        $query->setParameter("roomId", $roomId);
        $query->setParameter("rowNum", $rowNum);
        $query->setParameter("colStart", $colStart);
        $query->setParameter("colEnd", $colEnd);
        return $query->getResult();
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
