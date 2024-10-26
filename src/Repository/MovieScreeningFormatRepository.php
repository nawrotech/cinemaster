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
    * @return int[] Returns an array of ScreeningFormat ids
    */
   public function findScreeningFormatIdsByMovie(Movie $movie, Cinema $cinema) {
        $result = $this->createQueryBuilder('msf')
                    ->innerJoin("msf.screeningFormat", "sf")
                    ->select("sf.id")
                    ->andWhere("msf.cinema = :cinema")
                    ->andWhere("msf.movie = :movie")
                    ->setParameter("movie", $movie)
                    ->setParameter("cinema", $cinema)
                    ->getQuery()
                    ->getResult()
        ;
        return array_column($result, "id");
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
