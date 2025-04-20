<?php

namespace App\Service;

use App\Dto\MovieDetailsDto;
use App\Entity\Movie;

class MovieDataMerger {

    public function __construct(
        private TmdbApiService $tmdbApiService,
        )
    {
    }

    public function mergeWithApiData(Movie $movie): MovieDetailsDto|Movie {

        if (!$movie->getTmdbId()) {
            return $movie;
        }
        
        $apiData = $this->tmdbApiService->cacheMovie($movie->getTmdbId());
    
        return  MovieDetailsDto::fromMovieAndApi($movie, $apiData);
     
    }


}