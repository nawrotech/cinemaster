<?php

namespace App\Repository;

use App\Entity\Cinema;
use App\Entity\ScreeningFormat;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<MovieType>
 */
class ScreeningFormatRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ScreeningFormat::class);
    }

    
       public function findByIds(array $screeningFormatIds): array
       {
           return $this->createQueryBuilder('sf')
                ->andWhere("sf.if IN :screeningFormatIds")
                ->setParameter("screeningFormatIds", $screeningFormatIds)
                ->getQuery()
                ->getResult()
           ;
       }

    //    public function findOneBySomeField($cinema): ?MovieType
    //    {
    //        return $this->createQueryBuilder('sf')
    //            ->andWhere('sf.exampleField = :val')
    //            ->setParameter('val', $cinema)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
