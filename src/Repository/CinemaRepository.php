<?php

namespace App\Repository;

use App\Entity\Cinema;
use App\Entity\User;
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


    public function findOrderedCinemas(User $owner)
    {
        return $this->createQueryBuilder('c')
            ->orderBy("c.name", "DESC")
            ->andWhere("c.owner = :owner")
            ->setParameter("owner", $owner)
            ->getQuery()
            ->getResult();
    }



}
