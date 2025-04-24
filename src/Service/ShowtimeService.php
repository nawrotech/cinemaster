<?php

namespace App\Service;

use App\Entity\Cinema;
use App\Entity\Movie;
use App\Entity\ReservationSeat;
use App\Entity\Showtime;
use App\Repository\ScreeningRoomSeatRepository;
use App\Repository\ShowtimeRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

class ShowtimeService 
{

    public function __construct(
        private ShowtimeRepository $showtimeRepository,
        private ScreeningRoomSeatRepository $screeningRoomSeatRepository,
        private EntityManagerInterface $em,
        #[Autowire('%timezone_offset_hours%')] private int $timezoneOffsetHours
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
        ?\DateTimeImmutable $startDate = null,
        ?\DateTimeImmutable $endDate = null
    ) 
    {
        $startDate ??= new \DateTimeImmutable('now')->modify("+{$this->timezoneOffsetHours} hours");
        $endDate ??= new \DateTimeImmutable('now')->setTime(23, 59, 59);


        if ($endDate < $startDate) {
            throw new \InvalidArgumentException('End date cannot be before start date');
        }

        $showtimes = $this->showtimeRepository
                                    ->findFiltered($cinema, 
                                        showtimeStartTime: $startDate, 
                                        showtimeEndTime: $endDate,
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

    public function getPublishedShowtimesGroupedByMovieAndDate(
        Cinema $cinema, 
        Movie $movie,
        ?\DateTimeImmutable $startDate = null,
        ?\DateTimeImmutable $endDate = null,
        
        ) {

        if ($endDate < $startDate) {
            throw new \InvalidArgumentException('End date cannot be before start date');
        }
    
        $startDate ??= new \DateTimeImmutable('now')->modify("+{$this->timezoneOffsetHours} hours");
        $endDate ??= new \DateTimeImmutable('+7 days')->setTime(23, 59, 59);

        $showtimes = $this->showtimeRepository->findShowtimesInRangeForMovie(
            $cinema, 
            $movie, 
            $startDate, 
            $endDate
        );

        return array_reduce(    
            $showtimes,
            function ($grouped, $showtime) {
                $grouped[$showtime['startsAt']->format('Y-m-d')][] = $showtime;
                return $grouped;
            },
            []
        );
    }

    public function getMovieIdsForPublishedShowtimes(
        Cinema $cinema,
        ?\DateTimeImmutable $startDate = null,
        ?\DateTimeImmutable $endDate = null
    ) {

        if ($endDate < $startDate) {
            throw new \InvalidArgumentException('End date cannot be before start date');
        }
        
        $startDate ??= new \DateTimeImmutable('now')->modify("+{$this->timezoneOffsetHours} hours");
        $endDate ??= new \DateTimeImmutable('+7 days')->setTime(23, 59, 59);

        $movieIds = $this->showtimeRepository->findMovieIdsForPublishedShowtimes($cinema, $startDate, $endDate);

        return $movieIds;
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