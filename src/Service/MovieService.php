<?php

namespace App\Service;

use App\Entity\Cinema;
use App\Entity\Movie;
use Doctrine\ORM\EntityManagerInterface;

class MovieService {


    public function __construct(
        private EntityManagerInterface $em,
        private TmdbApiService $tmdbApiService
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
}