<?php

namespace App\Repository;

use App\Entity\Cinema;
use App\Entity\CinemaSeat;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
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
            ->leftJoin("cs.seat", "s")
            ->andWhere("s.rowNum BETWEEN 1 AND :maxRowNum")
            ->andWhere("s.colNum BETWEEN 1 AND :maxColNum")
            ->andWhere("cs.cinema = :cinema")
            ->setParameter("cinema", $cinema)
            ->setParameter("maxRowNum", $maxRowNum)
            ->setParameter("maxColNum", $maxColNum)
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
