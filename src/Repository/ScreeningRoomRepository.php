<?php

namespace App\Repository;

use App\Entity\Cinema;
use App\Entity\ScreeningRoom;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ScreeningRoom>
 */
class ScreeningRoomRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ScreeningRoom::class);
    }
    
    
    public function findDistinctRoomNames(Cinema $cinema): array
       {
           return array_column($this->createQueryBuilder('sr')
                    ->select("sr.name")
                    ->distinct()
                    ->andWhere("sr.cinema = :cinema")
                    ->setParameter("cinema", $cinema)
                    ->getQuery()
                    ->getScalarResult(), "name")
           ;
       }

}
