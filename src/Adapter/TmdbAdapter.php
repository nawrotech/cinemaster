<?php

namespace App\Adapter;

use Pagerfanta\Adapter\AdapterInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class TmdbAdapter implements AdapterInterface {

    const int MAX_PER_PAGE = 20;

    public function __construct(
        private HttpClientInterface $client, 
        private string $url,
        private array $parameters,
        private string $tmdbApiKey,
    )
    {
    }

    public function getNbResults(): int {

        $response = $this->client->request('GET', $this->url, [
            'query' => array_merge($this->parameters, [
                "page" => 1,
                "api_key" => $this->tmdbApiKey,
            ]),
        ]);

        $data = $response->toArray();
        $totalResults = $data['total_results'] ?? 0;

        $totalPages = min(ceil($totalResults / 20), 500);

        return $totalPages * 20;

    }

    public function getSlice(int $offset, int $length): iterable {
        $page = intval($offset / $length) + 1; 
    
        $response = $this->client->request("GET", $this->url, [
            "query" => array_merge($this->parameters, [
                "page" => $page, 
                "api_key" => $this->tmdbApiKey,
                ])
        ]);

        $data = $response->toArray();
        return $data["results"] ?? []; 
    }

}