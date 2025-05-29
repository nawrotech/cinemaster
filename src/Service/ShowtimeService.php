<?php

namespace App\Service;

use App\Entity\Cinema;
use App\Entity\Movie;
use App\Entity\ReservationSeat;
use App\Entity\Showtime;
use App\Repository\ScreeningRoomRepository;
use App\Repository\ScreeningRoomSeatRepository;
use App\Repository\ShowtimeRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class ShowtimeService
{

    public function __construct(
        private ShowtimeRepository $showtimeRepository,
        private ScreeningRoomSeatRepository $screeningRoomSeatRepository,
        private EntityManagerInterface $em,
        private ValidatorInterface $validator,
        #[Autowire('%timezone_offset_hours%')] private int $timezoneOffsetHours,
    ) {}

    /**
     * Retrieves published showtimes by date and groups them by movie ID
     * @param array $movieIds Array of movie IDs to filter by
     * @return array Associative array with movie IDs as keys and arrays of showtimes as values
     */
    public function getPublishedShowtimesGroupedByMovie(
        Cinema $cinema,
        array $movieIds,
        ?\DateTimeImmutable $startDate = null,
    ) {
        $startDate ??= (new \DateTimeImmutable('now'))->modify("+{$this->timezoneOffsetHours} hours");
        $cinemaOpenHour = (int) $cinema->getOpenTime()->format('H');

        $showtimes = $this->showtimeRepository->findPublishedShowtimesByCinemaAndMovies(
            $cinema,
            $movieIds,
            $cinema->getOpenTime()->format('H'),
            $startDate
        );

        $groupedShowtimes = array_reduce(
            $showtimes,
            function ($grouped, $showtime) {
                $movieId = $showtime->getMovieScreeningFormat()->getMovie()->getId();
                $grouped[$movieId][] = $showtime;
                return $grouped;
            },
            []
        );

        foreach ($groupedShowtimes as $movieId => $movieShowtimes) {
            usort($movieShowtimes, function ($a, $b) use ($cinemaOpenHour) {
                $hourA = (int)$a->getStartsAt()->format('H');
                $hourB = (int)$b->getStartsAt()->format('H');

                $orderA = $hourA < $cinemaOpenHour ? $hourA + 24 : $hourA;
                $orderB = $hourB < $cinemaOpenHour ? $hourB + 24 : $hourB;

                return $orderA <=> $orderB;
            });

            $groupedShowtimes[$movieId] = $movieShowtimes;
        }

        return $groupedShowtimes;
    }

    public function getMovieIdsForPublishedShowtimes(
        Cinema $cinema,
        ?\DateTimeImmutable $startDate = null,
        ?\DateTimeImmutable $endDate = null,
    ) {

        $startDate ??= (new \DateTimeImmutable())->modify("+{$this->timezoneOffsetHours} hours");
        $endDate ??= (new \DateTimeImmutable('+7 days'))->format('Y-m-d');

        $movieIds = $this->showtimeRepository
            ->findMovieIdsForPublishedShowtimes(
                $cinema,
                $startDate,
                $endDate,
                $cinema->getOpenTime()->format("H")
            );

        return $movieIds;
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

        $startDate ??= (new \DateTimeImmutable('now'))->modify("+{$this->timezoneOffsetHours} hours");
        $endDate ??= (new \DateTimeImmutable('+7 days'))->setTime(23, 59, 59);

        $showtimes = $this->showtimeRepository->findUpcomingShowtimesForTheMovie(
            $cinema,
            $movie,
            $cinema->getOpenTime()->format("H"),
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


    public function publishShowtime(Showtime $showtime): void
    {

        $this->em->wrapInTransaction(function ($em) use ($showtime) {

            $screeningRoomSeatQuery = $this->screeningRoomSeatRepository
                ->findByScreeningRoomQuery($showtime->getScreeningRoom());

            $batchSize = 100;
            $i = 0;

            foreach ($screeningRoomSeatQuery->toIterable() as $showtimeRoomSeat) {

                $reservationSeat = new ReservationSeat();
                $reservationSeat->setShowtime($showtime);
                $reservationSeat->setSeat($showtimeRoomSeat);
                $reservationSeat->setStatus($showtimeRoomSeat->getStatus());

                $priceTier = $showtimeRoomSeat->getPriceTier();
                if ($priceTier) {
                    $reservationSeat->setOriginalPriceTier($priceTier);
                    $reservationSeat->setPriceTierType($priceTier->getType());
                    $reservationSeat->setPriceTierPrice($priceTier->getPrice());
                    $reservationSeat->setPriceTierColor($priceTier->getColor());
                }
                ++$i;
                $em->persist($reservationSeat);
                if (($i % $batchSize) === 0) {
                    $em->flush();
                    foreach ($this->em->getUnitOfWork()->getIdentityMap()['App\Entity\ReservationSeat'] ?? [] as $entity) {
                        $this->em->detach($entity);
                    }
                }
            }
            $showtime->setPublished(true);
            $em->flush();
            $em->clear();
        });
    }
}
