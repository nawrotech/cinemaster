<?php

namespace App\Service;

use App\Entity\Cinema;
use App\Repository\ShowtimeRepository;

class ShowtimeService 
{

    public function __construct(private ShowtimeRepository $showtimeRepository)
    {
    }

    /**
     * Retrieves published showtimes by date and groups them by movie ID
     * @param array $movieIds Array of movie IDs to filter by
     * @return array Associative array with movie IDs as keys and arrays of showtimes as values
     */
    public function getPublishedShowtimesGroupedByMovie(
        Cinema $cinema,
        array $movieIds,
        ?string $date = null
    ) 
    {
        $showtimes = $this->showtimeRepository
                                    ->findFiltered($cinema, 
                                        date: $date, 
                                        isPublished: true,
                                        movieIds: $movieIds
                                    );

        return array_reduce(
            $showtimes,
            function ($grouped, $showtime) {
                $movieId = $showtime->getMovieScreeningFormat()->getMovie()->getId();
                $grouped[$movieId][] = $showtime;
                return $grouped;
            },
            []
        );
    }
}