<?php

namespace App\Repository;

use App\Entity\MovieMovieType;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<MovieMovieType>
 */
class MovieMovieTypeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, MovieMovieType::class);
    }

   
       public function findMovieWithFormats(): array
       {
           return $this->createQueryBuilder('mmt')
                ->innerJoin("mmt.movie", "m")
                ->innerJoin("mmt.movieType", "mt")
                ->select("mmt")
               ->getQuery()
               ->getResult()
           ;
       }

    //    public function findOneBySomeField($value): ?MovieMovieType
    //    {
    //        return $this->createQueryBuilder('m')
    //            ->andWhere('m.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
