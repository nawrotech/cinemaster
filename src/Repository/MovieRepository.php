<?php

namespace App\Repository;

use App\Entity\Cinema;
use App\Entity\Movie;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Movie>
 */
class MovieRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Movie::class);
    }

    /**
    * @return Movie[]|\Doctrine\ORM\QueryBuilder 
    */
    public function findBySearchTerm(Cinema $cinema, ?string $searchTerm = null, $returnQueryBuilder = false): array|QueryBuilder
    {
        $queryBuilder = $this->createQueryBuilder('m');
          
        if ($searchTerm) {
            $queryBuilder = $queryBuilder->andWhere('m.title LIKE :searchTerm')
                                        ->andWhere("m.cinema = :cinema")
                                        ->setParameter('searchTerm', "%$searchTerm%")
                                        ->setParameter("cinema", $cinema);
        }

        if ($returnQueryBuilder) {
            return $queryBuilder;
        }

        return $queryBuilder->getQuery()
                            ->getResult();
    }

    /**
    * @return int[] returns array of tmdbIds for cinema
    */
    public function findTmdbIdsForCinema(Cinema $cinema): array {
        return $this->createQueryBuilder('m')
                    ->select("m.tmdbId")
                    ->where("m.cinema = :cinema")
                    ->setParameter("cinema", $cinema)
                    ->getQuery()
                    ->getSingleColumnResult();
    }

    /**
    * @return Movie[] 
    */
    public function findDistinctMovie(Cinema $cinema): array {
        return $this->createQueryBuilder('m')
                    ->innerJoin("m.movieScreeningFormats", "msf")

                    ->andWhere("m.cinema = :cinema")
                    ->setParameter("cinema", $cinema)
                    ->getQuery()
                    ->getSingleColumnResult();
    }

//     return $this->createQueryBuilder("s")
//     ->innerJoin("s.movieScreeningFormat", "msf")
//     ->innerJoin("msf.movie", "m")
//     ->innerJoin("s.screeningRoom", "sr")
//     ->select("m")
//     ->distinct()
//     ->andWhere("s.isPublished = :isPublished")
//     ->andWhere("sr.cinema = :cinema")
//     ->setParameter("cinema", $cinema)
//     ->setParameter("isPublished", $isPublished)
//     ->getQuery()
//     ->getResult()
// ;

}
