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


    public function findOrderedCinemas(bool $visible = true)
    {
        return $this->createQueryBuilder('c')
            ->addSelect("cs")
            ->innerJoin("c.cinemaSeats", "cs")
            ->orderBy("c.name", "DESC")
            ->andWhere("cs.visible = :visible")
            ->setParameter("visible", $visible)
            ->getQuery()
            ->getResult();
    }



}
