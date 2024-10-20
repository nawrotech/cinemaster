<?php

namespace App\Repository;

use App\Entity\MovieFormat;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<MovieFormat>
 */
class MovieFormatRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, MovieFormat::class);
    }

   /**
    * @return Movie|Format[] Returns an array of MovieFormat objects
    */
   public function findMovieWithFormats(): array
   {
       return $this->createQueryBuilder('mf')
            ->addSelect("m")
            ->addSelect("f")
            ->innerJoin("mf.movie", "m")
            ->innerJoin("mf.format", "f")
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
       ;
   }


}
