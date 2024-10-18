<?php

namespace App\Repository;

use App\Entity\Cinema;
use App\Entity\CinemaSeat;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Collections\Criteria;
use Doctrine\Common\Collections\Expr\Comparison;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<CinemaSeat>
 */
class CinemaSeatRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CinemaSeat::class);
    }

    

    public function findCinemaSeatsInRange(int $maxRowNum, int $maxColNum, Cinema $cinema)
    {
        return $this->createQueryBuilder('cs')
            ->innerJoin("cs.seat", "s")
            ->andWhere("s.rowNum BETWEEN 1 AND :maxRowNum")
            ->andWhere("s.colNum BETWEEN 1 AND :maxColNum")
            ->andWhere("cs.cinema = :cinema")
            ->setParameter("cinema", $cinema)
            ->setParameter("maxRowNum", $maxRowNum)
            ->setParameter("maxColNum", $maxColNum)
            ->getQuery()
            ->getResult();
    }

    public function findLastSeat(Cinema $cinema, bool $visible = true)
    {
        return $this->createQueryBuilder('cs')
            ->innerJoin("cs.seat", "s")
            ->select("MAX(s.rowNum) as lastRowNum, MAX(s.seatNumInRow) as lastSeatNumInRow")
            ->andWhere("cs.cinema = :cinema")
            ->andWhere("cs.visible = :visible")
            ->setParameter("cinema", $cinema)
            ->setParameter("visible", $visible)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function findSeatRowsInRange(Cinema $cinema, int $rowStart, int $rowEnd)
    {
        return $this->createQueryBuilder('cs')
            ->innerJoin("cs.seat", "s") // temp
            ->addSelect("s")
            ->andWhere("s.rowNum BETWEEN :rowStart AND :rowEnd")
            ->andWhere("cs.cinema = :cinema")
            ->setParameter("cinema", $cinema)
            ->setParameter("rowStart", $rowStart)
            ->setParameter("rowEnd", $rowEnd)
            ->getQuery()
            ->getResult();
    }

    public function findSeatColsInRange(Cinema $cinema, int $seatInRowStart, int $seatInRowEnd)
    {
        return $this->createQueryBuilder('cs')
            ->innerJoin("cs.seat", "s") // temp
            ->addSelect("s")
            ->andWhere("s.colNum BETWEEN :seatInRowStart AND :seatInRowEnd")
            ->andWhere("cs.cinema = :cinema")
            ->setParameter("cinema", $cinema)
            ->setParameter("seatInRowStart", $seatInRowStart)
            ->setParameter("seatInRowEnd", $seatInRowEnd)
            ->getQuery()
            ->getResult();
    }
    
    
    public function findSeatsInRange(
        Cinema $cinema,
        int $rowStart,
        int $rowEnd,
        int $seatInRowStart,
        int $seatInRowEnd,
        bool $excludeFirstRow = false,
        bool $excludeFirstSeatInRow = false
    ) {

        $qb = $this->createQueryBuilder('cs')
            ->innerJoin("cs.seat", "s")
            ->addSelect("s")
            ->andWhere("s.rowNum BETWEEN :rowStart AND :rowEnd")
            ->andWhere("s.seatNumInRow BETWEEN :seatInRowStart AND :seatInRowEnd")
            ->andWhere("cs.cinema = :cinema")
            ->setParameter("cinema", $cinema)
            ->setParameter("rowStart", $rowStart)
            ->setParameter("rowEnd", $rowEnd)
            ->setParameter("seatInRowStart", $seatInRowStart)
            ->setParameter("seatInRowEnd", $seatInRowEnd);
           

        if ($excludeFirstRow) {
            $qb->andWhere("s.rowNum != :rowStart");
        }

        if ($excludeFirstSeatInRow) {
            $qb->andWhere("s.seatNumInRow != :seatInRowStart");
        }

        return $qb->getQuery()
                    ->getResult();
    }


    public static function visibleSeatsCriterion()
    {
        $exp = new Comparison("visible", Comparison::EQ, true);
        return Criteria::create()->andWhere($exp);
    }

    public function findSeatsForCinema(Cinema $cinema, $status = "active")
    {
        return $this->createQueryBuilder('cs')
            ->innerJoin("cs.seat", "s")
            ->addSelect("s")
            ->andWhere("cs.status = :status")
            ->andWhere("cs.cinema = :cinema")
            ->setParameter("cinema", $cinema)
            ->setParameter("status", $status)
            ->getQuery()
            ->getResult();
    }

}
