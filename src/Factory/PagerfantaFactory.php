<?php

namespace App\Factory;

use App\Adapter\TmdbAdapter;
use App\Entity\Movie;
use App\Repository\ShowtimeRepository;
use App\Service\MovieDataMerger;
use Doctrine\ORM\QueryBuilder;
use Pagerfanta\Adapter\ArrayAdapter;
use Pagerfanta\Doctrine\ORM\QueryAdapter;
use Pagerfanta\Pagerfanta;
use Pagerfanta\PagerfantaInterface;

class PagerfantaFactory
{

    public function __construct(
        private TmdbAdapterFactory $tmdbAdapterFactory,
        private MovieDataMerger $movieDataMerger
    ) {}

    public function createTmdbPagerfanta(string $q, int $page,): PagerfantaInterface
    {

        $endpoint = $q ? "search/movie" : "movie/now_playing";
        $params = $q ? ["query" => $q] : [];

        $adapter = $this->tmdbAdapterFactory->create($endpoint, $params);
        $pagerfanta = new Pagerfanta($adapter);

        $currentPage = max(1, $page);
        $pagerfanta->setCurrentPage($currentPage);
        $pagerfanta->setMaxPerPage(TmdbAdapter::MAX_PER_PAGE);

        return $pagerfanta;
    }

    public function createAvailableMoviesPagerfanta(array $movies, int $page): PagerfantaInterface
    {

        $mergedMovies = array_map(function (Movie $movie) {
            return $this->movieDataMerger->mergeWithApiData($movie);
        }, $movies);

        $adapter = new ArrayAdapter($mergedMovies);
        $pagerfanta = new Pagerfanta($adapter);

        $pagerfanta->setMaxPerPage(12);
        $pagerfanta->setCurrentPage($page);

        return $pagerfanta;
    }

    public function createShowtimesPagerfanta(QueryBuilder $showtimes, int $page): PagerfantaInterface
    {
        $adapter = new QueryAdapter($showtimes);
        $pagerfanta = new Pagerfanta($adapter);

        $pagerfanta->setMaxPerPage(ShowtimeRepository::MAX_PER_PAGE);
        $pagerfanta->setCurrentPage($page);

        return $pagerfanta;
    }
}
