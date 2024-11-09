<?php

namespace App\Repository;

use App\Entity\Cinema;
use App\Entity\Movie;
use App\Entity\MovieScreeningFormat;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<MovieScreeningFormat>
 */
class MovieScreeningFormatRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, MovieScreeningFormat::class);
    }

   /**
    * @return Movie|ScreeningFormat[]
    */
   public function findMovieWithFormats(): array
   {
       return $this->createQueryBuilder('msf')
            ->addSelect("m")
            ->addSelect("sf")
            ->innerJoin("msf.movie", "m")
            ->innerJoin("msf.screeningFormat", "sf")
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
       ;
   }

    /**
    * @return MovieScreeningFormat[] Returns an array of MovieScreeningFormats for movie
    */
   public function findScreeningFormatsForMovie(Movie $movie) {
          return $this->createQueryBuilder('msf')
                    ->innerJoin("msf.screeningFormat", "sf")
                    ->addSelect("sf")
                    ->andWhere("msf.movie = :movie")
                    ->setParameter("movie", $movie)
                    ->getQuery()
                    ->getResult()
        ;
   }

    /**
    * @return ScreeningFormat[] 
    */
   public function findByScreeningFormatIds(array $screeningFormatIds, Movie $movie) {
        return $this->createQueryBuilder('msf')
                    ->innerJoin("msf.screeningFormat", "sf")
                    ->andWhere("sf.id IN (:screeningFormatIds)")
                    ->andWhere("msf.movie = :movie")
                    ->setParameter("screeningFormatIds", $screeningFormatIds)
                    ->setParameter("movie", $movie)
                    ->getQuery()
                    ->getResult()
        ;
   }


}
