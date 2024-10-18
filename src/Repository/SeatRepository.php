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
            ->select("MAX(s.rowNum) AS maxRowNum", "MAX(s.seatNumInRow) AS maxSeatNumInRow")
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }

    /**
     * @return Seat[]
     */
    public function findSeatsInRange(
        int $rowStart, 
        int $rowEnd, 
        int $seatInRowStart, 
        int $seatInRowEnd): array
    {
        return $this->createQueryBuilder("s")
            ->andWhere("s.rowNum BETWEEN :rowStart AND :rowEnd")
            ->andWhere("s.seatNumInRow BETWEEN :seatInRowStart AND :seatInRowEnd")
            ->setParameter("rowStart", $rowStart)
            ->setParameter("rowEnd", $rowEnd)
            ->setParameter("seatInRowStart", $seatInRowStart)
            ->setParameter("seatInRowEnd", $seatInRowEnd)
            ->addOrderBy("s.rowNum", "ASC")
            ->addOrderBy("s.seatNumInRow", "ASC")
            ->getQuery()
            ->getResult();
    }


    


}
