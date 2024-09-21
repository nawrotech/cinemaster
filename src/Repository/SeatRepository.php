<?php

namespace App\Repository;

use App\Entity\Seat;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Seat>
 */
class SeatRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Seat::class);
    }

    //    /**
    //     * @return Seat[] Returns an array of Seat objects
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

    //    public function findOneBySomeField($value): ?Seat
    //    {
    //        return $this->createQueryBuilder('s')
    //            ->andWhere('s.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }

    public function findMax()
    {
        return $this->createQueryBuilder('s')
            ->select("COUNT(DISTINCT s.rowNum) AS maxRowNum", "COUNT(DISTINCT s.colNum) AS maxColNum")
            ->getQuery()
            ->getResult()
        ;
    }

    public function findSeatsInRange(int $maxRowNum, int $maxColNum)
    {
        return $this->createQueryBuilder('s')
            ->andWhere("s.rowNum BETWEEN 1 AND :maxRowNum")
            ->andWhere("s.colNum BETWEEN 1 AND :maxColNum")
            ->setParameter("maxRowNum", $maxRowNum)
            ->setParameter("maxColNum", $maxColNum)
            ->getQuery()
            ->getResult();
    }
}
