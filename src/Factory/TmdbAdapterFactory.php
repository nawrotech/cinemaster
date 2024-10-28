<?php

namespace App\Factory;

use App\Adapter\TmdbAdapter;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class TmdbAdapterFactory {

    public function __construct(
        private HttpClientInterface $client,
        #[Autowire(env: "TMDB_API_KEY")]
        private string $tmdbApiKey) {
    }

    public function create(string $endpoint, array $parameters = []) {

        $url = "https://api.themoviedb.org/3/$endpoint";
        $parameters = array($parameters, [
            "api_key" => $this->tmdbApiKey,
        ]);

        return new TmdbAdapter($this->client, $url, $parameters, $this->tmdbApiKey);
    }
}