<?php

namespace App\Service;

use App\Entity\Cinema;
use App\Entity\Movie;
use App\Repository\MovieRepository;
use Doctrine\ORM\EntityManagerInterface;

class MovieService 
{

    public function __construct(
        private EntityManagerInterface $em,
        private TmdbApiService $tmdbApiService,
        private MovieRepository $movieRepository,
        private MovieDataMerger $movieDataMerger
        )
    {
    }

    public function createMovie(int $tmdbId, Cinema $cinema) 
    {
        $movieTmdbDto = $this->tmdbApiService->cacheMovie($tmdbId);
        
        $movie = new Movie();
        $movie->setTmdbId($tmdbId);
        $movie->setTitle($movieTmdbDto->getTitle());
        $movie->setDurationInMinutes($movieTmdbDto->getDurationInMinutes());
        $movie->setCinema($cinema);
        $this->em->persist($movie);
        $this->em->flush();
    }

    /**
     * Get enriched movie data for the given movie IDs
     *
     * @param array $movieIds Array of movie IDs to retrieve
     * @return array Associative array of movie ID => enriched movie data
     */
    public function getEnrichedMoviesByIds(array $movieIds): array
    {
        if (empty($movieIds)) {
            return [];
        }
        
        $movies = $this->movieRepository->findBy(["id" => $movieIds]);
        
        return array_reduce($movies, function ($carry, $movie) {
            $carry[$movie->getId()] = $this->movieDataMerger->mergeWithApiData($movie);
            return $carry;
        }, []);
    }
}