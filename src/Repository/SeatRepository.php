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


    public function findMax()
    {
        return $this->createQueryBuilder('s')
            ->select("MAX(s.rowNum AS maxRowNum)", "MAX(s.colNum) AS maxColNum")
            ->getQuery()
            ->getResult()
        ;
    }

    public function findSeatsInRange(int $rowStart, int $rowEnd, int $colStart, int $colEnd)
    {
        return $this->createQueryBuilder("s")
            ->andWhere("s.rowNum BETWEEN :rowStart AND :rowEnd")
            ->andWhere("s.colNum BETWEEN :colStart AND :colEnd")
            ->setParameter("rowStart", $rowStart)
            ->setParameter("rowEnd", $rowEnd)
            ->setParameter("colStart", $colStart)
            ->setParameter("colEnd", $colEnd)
            ->getQuery()
            ->getResult();
    }
}
