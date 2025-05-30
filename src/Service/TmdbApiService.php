<?php

namespace App\Service;

use App\Dto\TmdbMovieDto;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class TmdbApiService
{
    public function __construct(
        private HttpClientInterface $client,
        private CacheInterface $cache,
        #[Autowire(env: "TMDB_API_KEY")]
        private string $tmdbApiKey
    ) {}



    public function cacheMovie(int $tmdbId): TmdbMovieDto
    {
        $cacheKey = "tmdb_movie" . $tmdbId;

        return $this->cache->get($cacheKey, function (ItemInterface $item) use ($tmdbId) {
            $item->expiresAfter(86400);

            $response = $this->client->request('GET', "https://api.themoviedb.org/3/movie/$tmdbId", [
                'query' => [
                    'api_key' =>  $this->tmdbApiKey,
                ],
                'timeout' => 5.0,
            ]);
            
            if ($response->getStatusCode() !== 200) {
                throw new \Exception("Failed to fetch movie data from TMDB");
            }

            $movieData = $response->toArray();
            return TmdbMovieDto::fromResponse($movieData);
        });
    }


    public function deleteMovie(int $tmdbId): void
    {
        $cacheKey = "tmdb_movie" . $tmdbId;
        $this->cache->delete($cacheKey);
    }
}
