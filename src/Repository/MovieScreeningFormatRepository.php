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
    * @return Movie|ScreeningFormat[] Returns an array of MovieScreeningFormat objects
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

   public function findByScreeningFormatIds(array $screeningFormatIds) {
        return $this->createQueryBuilder('msf')
                    ->innerJoin("msf.screeningFormat", "sf")
                    ->andWhere("sf.id IN (:screeningFormatIds)")
                    ->setParameter("screeningFormatIds", $screeningFormatIds)
                    ->getQuery()
                    ->getResult()
        ;
   }


}
