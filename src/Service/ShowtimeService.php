<?php

namespace App\Service;

use App\Entity\Cinema;
use App\Entity\ReservationSeat;
use App\Entity\Showtime;
use App\Repository\ScreeningRoomSeatRepository;
use App\Repository\ShowtimeRepository;
use Doctrine\ORM\EntityManagerInterface;

class ShowtimeService 
{

    public function __construct(
        private ShowtimeRepository $showtimeRepository,
        private ScreeningRoomSeatRepository $screeningRoomSeatRepository,
        private EntityManagerInterface $em
        )
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

    public function publishShowtime(Showtime $showtime) 
    {
        $showtimeRoomSeats = $this->screeningRoomSeatRepository->findBy(
            ["screeningRoom" => $showtime->getScreeningRoom()]
        );
        $this->em->wrapInTransaction(function ($em) use ($showtime, $showtimeRoomSeats) {
            foreach ($showtimeRoomSeats as $showtimeRoomSeat) {
                $reservationSeat = new ReservationSeat();
                $reservationSeat->setShowtime($showtime);
                $reservationSeat->setSeat($showtimeRoomSeat);
                $reservationSeat->setStatus($showtimeRoomSeat->getStatus());
                $em->persist($reservationSeat);
            }
            $showtime->setPublished(true);
            $em->flush();
        });
    }
}