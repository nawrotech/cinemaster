<?php

namespace App\Repository;

use App\Entity\Cinema;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Cinema>
 */
class CinemaRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Cinema::class);
    }


    public function findOrderedCinemas()
    {
        return $this->createQueryBuilder('c')
            ->addSelect("cs")
            ->innerJoin("c.cinemaSeats", "cs")
            ->orderBy("c.name", "DESC")
            ->getQuery()
            ->getResult();
    }

    public function findMax(Cinema $cinema)
    {
        return $this->createQueryBuilder('c')
            ->leftJoin("c.cinemaSeats", "cs")
            ->leftJoin("cs.seat", "s")
            ->select("COUNT(DISTINCT s.rowNum) AS maxRowNum", "COUNT(DISTINCT s.colNum) AS maxColNum")
            ->andWhere("c = :cinema")
            ->setParameter("cinema", $cinema)
            ->getQuery()
            ->getSingleResult();
    }



    //    /**
    //     * @return Cinema[] Returns an array of Cinema objects
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

    //    public function findOneBySomeField($value): ?Cinema
    //    {
    //        return $this->createQueryBuilder('c')
    //            ->andWhere('c.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
