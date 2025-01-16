<?php

namespace App\Tests;

use App\Adapter\TmdbAdapter;
use PHPUnit\Framework\TestCase;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

class TmdbAdapterTest extends TestCase
{
    public function testGetNbResultsReturnsCorrectTotal(): void
    {
        $httpClient = $this->createMock(HttpClientInterface::class);
        $response = $this->createMock(ResponseInterface::class);

        $httpClient->method('request')
            ->willReturn($response);

        $response->method('toArray')->willReturn([
            'total_results' => 1000
        ]);

        assert($httpClient instanceof HttpClientInterface);

        $adapter = new TmdbAdapter($httpClient, 'https://example.com/api', [], 'api_key');
        $totalResults = $adapter->getNbResults();

        $this->assertEquals(1000, $totalResults);
    }

    public function testGetNbResultsHandles500PageLimit(): void 
    {
        $httpClient = $this->createMock(HttpClientInterface::class);
        $response = $this->createMock(ResponseInterface::class);

        $httpClient->method('request')
            ->willReturn($response);
        
        $response->method('toArray')->willReturn(['total_results' => 20000]);

        assert($httpClient instanceof HttpClientInterface);

        $adapter = new TmdbAdapter($httpClient, 'https://example.com', [], 'api_key');
        $this->assertEquals(10000, $adapter->getNbResults());

    }

    public function testGetSliceRetursResult(): void {
        $httpClient = $this->createMock(HttpClientInterface::class);
        $response = $this->createMock(ResponseInterface::class);

       
        $httpClient
            ->method("request")
            ->willReturn($response);

        $response->method('toArray')->willReturn([
            'results' => [
                ["id" => 1, "title" => "Movie 1"],
                ["id" => 2, "title" => "Movie 2"]
            ]
        ]);

        assert($httpClient instanceof HttpClientInterface);
        $adapter = new TmdbAdapter($httpClient, 'https://example.com/api', [], 'api_key');

        $results = $adapter->getSlice(20, 20);

        $this->assertCount(2, $results);
        $this->assertEquals('Movie 1', $results[0]['title']);
    }


    public function testGetSliceHandlesEmptyResults()
    {
        $httpClient = $this->createMock(HttpClientInterface::class);
        $response = $this->createMock(ResponseInterface::class);

        $httpClient->method('request')
            ->willReturn($response);

        $response->method('toArray')->willReturn([
            'results' => [],
        ]);

        assert($httpClient instanceof HttpClientInterface);

        $adapter = new TmdbAdapter($httpClient, 'https://example.com', [], 'api_key');
        $results = $adapter->getSlice(20, 20);

        $this->assertEmpty($results);
    }

}
