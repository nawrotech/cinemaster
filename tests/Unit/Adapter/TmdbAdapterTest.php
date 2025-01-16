<?php

namespace App\Tests;

use App\Adapter\TmdbAdapter;
use PHPUnit\Framework\TestCase;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

class TmdbAdapterTest extends TestCase
{
    public function testGetNbResultsReturnsCorrectTotal()
    {
        $httpClient = $this->createMock(HttpClientInterface::class);
        $response = $this->createMock(ResponseInterface::class);

        $httpClient->method('request')
            ->with('GET', 'https://example.com/api', $this->anything())
            ->willReturn($response);

        $response->method('toArray')->willReturn([
            'total_results' => 1000
        ]);

        assert($httpClient instanceof HttpClientInterface);

        $adapter = new TmdbAdapter($httpClient, 'https://example.com/api', [], 'api_key');
        $totalResults = $adapter->getNbResults();

        $this->assertEquals(1000, $totalResults);
        
    }

}
