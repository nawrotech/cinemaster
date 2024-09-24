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

    public function findSeatsInRange(int $maxRowNum, int $maxColNum, Cinema $cinema)
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

    public function findLastSeat(Cinema $cinema, string $status = "active")
    {
        return $this->createQueryBuilder('cs')
            ->innerJoin("cs.seat", "s")
            ->select("MAX(s.rowNum) as row, MAX(s.colNum) as col")
            ->andWhere("cs.cinema = :cinema")
            ->andWhere("cs.status = :status")
            ->setParameter("cinema", $cinema)
            ->setParameter(":status", $status)
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

    public function findSeatColsInRange(Cinema $cinema, int $colStart, int $colEnd)
    {
        return $this->createQueryBuilder('cs')
            ->innerJoin("cs.seat", "s") // temp
            ->addSelect("s")
            ->andWhere("s.colNum BETWEEN :colStart AND :colEnd")
            ->andWhere("cs.cinema = :cinema")
            ->setParameter("cinema", $cinema)
            ->setParameter("colStart", $colStart)
            ->setParameter("colEnd", $colEnd)
            ->getQuery()
            ->getResult();
    }

    public function findSeatsInGivenRange(
        Cinema $cinema,
        int $rowStart,
        int $rowEnd,
        int $colStart,
        int $colEnd
    ) {

        return $this->createQueryBuilder('cs')
            ->innerJoin("cs.seat", "s")
            ->addSelect("s")
            ->andWhere("s.rowNum BETWEEN :rowStart AND :rowEnd")
            ->andWhere("s.colNum BETWEEN :colStart AND :colEnd")
            ->andWhere("cs.cinema = :cinema")
            ->setParameter("cinema", $cinema)
            ->setParameter("rowStart", $rowStart)
            ->setParameter("rowEnd", $rowEnd)
            ->setParameter("colStart", $colStart)
            ->setParameter("colEnd", $colEnd)
            ->getQuery()
            ->getResult();
    }



    public static function activeSeatsCriterion()
    {
        $exp = new Comparison("status", Comparison::EQ, "active");
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



    //    /**
    //     * @return CinemaSeat[] Returns an array of CinemaSeat objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('c')
    //            ->andWhere('c.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('c.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?CinemaSeat
    //    {
    //        return $this->createQueryBuilder('c')
    //            ->andWhere('c.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
