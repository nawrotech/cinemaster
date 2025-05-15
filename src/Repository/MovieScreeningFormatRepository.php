<?php

namespace App\Repository;

use App\Entity\Cinema;
use App\Entity\Movie;
use App\Entity\MovieScreeningFormat;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
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

    public function findMovieScreeningFormatsForCinema(Cinema $cinema): array
    {
        $qb = $this->createQueryBuilder('msf')
            ->innerJoin('msf.screeningFormat', 'sf')
            ->innerJoin('sf.visualFormat', 'vf')
            ->select('DISTINCT vf.name');

        $qb = $this->filterByCinema($cinema, $qb);
        
        return $qb->getQuery()->getSingleColumnResult();
    }

    
    /**
     * @return MovieScreeningFormat[] Returns an array of MovieScreeningFormats for movie
     */
    public function findScreeningFormatsForMovie(Movie $movie): array
    {
        $qb = $this->createQueryBuilder('msf')
            ->innerJoin("msf.screeningFormat", "sf");

        $qb = $this->filterByMovie($movie, $qb);

        return $qb->getQuery()->getResult();
    }

    /**
     * @return ScreeningFormat[] 
     */
    public function findByScreeningFormatIds(array $screeningFormatIds, Movie $movie): array
    {
        $qb = $this->createQueryBuilder('msf')
            ->innerJoin("msf.screeningFormat", "sf")
            ->andWhere("sf.id IN (:screeningFormatIds)")
            ->setParameter("screeningFormatIds", $screeningFormatIds);
        
        $qb = $this->filterByMovie($movie, $qb);

        return $qb
            ->getQuery()
            ->getResult()
        ;
    }

    public function filterByMovie(Movie $movie,  ?QueryBuilder $qb = null): QueryBuilder 
    {
        return ($qb ?? $this->createQueryBuilder("msf"))
                    ->andWhere('msf.movie = :movie')
                    ->setParameter('movie', $movie);
    }


    public function filterByCinema(Cinema $cinema, ?QueryBuilder $qb = null): QueryBuilder 
    {
        return ($qb ?? $this->createQueryBuilder("msf"))
                    ->andWhere('msf.cinema = :cinema')
                    ->setParameter('cinema', $cinema);
    }
}
