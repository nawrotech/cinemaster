<?php

namespace App\Service;

use App\Entity\Movie;

class MovieDataMerger {

    public function __construct(
        private TmdbApiService $tmdbApiService,
        )
    {
    }

    public function mergeWithApiData(Movie $movie): array|Movie {

        
        if (!$movie->getTmdbId()) {
            return $movie;
        }
        
        $apiData = $this->tmdbApiService->cacheMovie($movie->getTmdbId());
    
        return [
            "id" => $movie->getId(),
            "title" => $movie->getTitle() ?? $apiData->getTitle(),
            "overview" => $movie->getOverview() ?? $apiData->getOverview(),
            "posterPath" => $movie->getPosterPath() ?? $apiData->getPosterPath(),
            "releaseDate" => $movie->getReleaseDate() ?? $apiData->getReleaseDate(),
            "durationInMinutes" => $movie->getDurationInMinutes() ?? $apiData->getDurationInMinutes(),
            "movieReferences" => $movie->getMovieReferences()
         ];
    }


}