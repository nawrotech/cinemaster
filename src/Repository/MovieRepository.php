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
     * @return Movie[]
     */
    public function findBySearchTerm(Cinema $cinema, ?string $searchTerm = null): array
    {
        return $this->createSearchQueryBuilder($cinema, $searchTerm)
            ->getQuery()
            ->getResult();
    }

    public function createSearchQueryBuilder(Cinema $cinema, ?string $searchTerm = null): QueryBuilder
    {
        $qb = $this->createQueryBuilder('m');
        $qb = $this->filterByCinema($cinema, $qb);

        if ($searchTerm) {
            $qb = $this->filterByTitle($searchTerm, $qb);
        }

        return $qb;
    }

    /**
     * @return int[] returns array of tmdbIds for cinema
     */
    public function findTmdbIdsForCinema(Cinema $cinema): array
    {
        $qb = $this->createQueryBuilder('m')
            ->select("m.tmdbId");
        
        $qb = $this->filterByCinema($cinema, $qb);

        return $qb->getQuery()->getSingleColumnResult();      
    }

    public function filterByCinema(Cinema $cinema, ?QueryBuilder $qb = null): QueryBuilder {
        return ($qb ?? $this->createQueryBuilder("m"))
                    ->andWhere('m.cinema = :cinema')
                    ->setParameter('cinema', $cinema);
    }

    public function filterByTitle(string $searchTerm, ?QueryBuilder $qb = null): QueryBuilder {
        return ($qb ?? $this->createQueryBuilder("m"))
                    ->andWhere('LOWER(m.title) LIKE LOWER(:searchTerm)')
                    ->setParameter('searchTerm', "%$searchTerm%");
    }


}
