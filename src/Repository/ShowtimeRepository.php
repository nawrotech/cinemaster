<?php

namespace App\Repository;

use App\Entity\Cinema;
use App\Entity\Movie;
use App\Entity\MovieScreeningFormat;
use App\Entity\ScreeningRoom;
use App\Entity\Showtime;
use DateTimeImmutable;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Showtime>
 */
class ShowtimeRepository extends ServiceEntityRepository
{

    public const MAX_PER_PAGE = 20;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Showtime::class);
    }

    public function findOverlapping(
        Cinema $cinema,
        string $date,
        \DateTimeImmutable $startsAt,
        \DateTimeImmutable $endsAt,
        ?Showtime $excludeShowtime = null,
    ): QueryBuilder {
        $qb = $this->createQueryBuilder('s')
            ->andWhere("(
                (:startsAt >= s.startsAt AND :startsAt < s.endsAt) OR
                (:endsAt > s.startsAt AND :endsAt <= s.endsAt) OR
                (:startsAt <= s.startsAt AND :endsAt >= s.endsAt)
            )")
            ->setParameter('startsAt', $startsAt)
            ->setParameter('endsAt', $endsAt);

        $qb = $this->filterByStartsAtDate($date, $qb);

        $qb = $this->filterByCinema($cinema, $qb);

        if ($excludeShowtime) {
            $qb->andWhere('s != :excludeShowtime')
                ->setParameter('excludeShowtime', $excludeShowtime);
        }
        return $qb;
    }


    /**
     * @return Showtime[] Returns showtimes overlapping in the same room
     */
    public function findOverlappingForRoom(
        ScreeningRoom $screeningRoom,
        Cinema $cinema,
        string $date,
        \DateTimeImmutable $startsAt,
        \DateTimeImmutable $endsAt,
        ?Showtime $excludeShowtime = null,
    ): array {
        $qb = $this->findOverlapping($cinema, $date, $startsAt, $endsAt, $excludeShowtime);

        $qb = $this->filterByScreeningRoom($screeningRoom, $qb);

        return $qb
            ->getQuery()
            ->getResult();
    }

    /**
     * @return Showtime[] returns showtimes where samve movie was scheduled in different room
     */
    public function findOverlappingForMovie(
        MovieScreeningFormat $movieScreeningFormat,
        Cinema $cinema,
        string $date,
        \DateTimeImmutable $startsAt,
        \DateTimeImmutable $endsAt,
        ?Showtime $excludeShowtime = null,
    ): array {
        return $this->findOverlapping($cinema, $date, $startsAt, $endsAt, $excludeShowtime)
            ->andWhere('s.movieScreeningFormat = :movieScreeningFormat')
            ->setParameter("movieScreeningFormat", $movieScreeningFormat)
            ->getQuery()
            ->getResult();
    }


    public function createFilteredQueryBuilder(
        ?Cinema $cinema = null,
        ?string $screeningRoomName = null,
        ?\DateTimeImmutable $startsAfterDateTime = null,
        ?\DateTimeImmutable $startsBeforeDateTime = null,
        ?string $movieTitle = null,
        ?bool $isPublished = null,
    ): QueryBuilder {
        $qb = $this->createQueryBuilder('s');

        if ($cinema != null) {
            $qb = $this->filterByCinema($cinema, $qb);
        }

        if ($screeningRoomName != null) {
            $qb = $this->filterByScreeningRoomName($screeningRoomName, $qb);
        }

        if ($startsAfterDateTime != null) {
            $qb = $this->filterByStartsAtAfter($startsAfterDateTime, $qb);
        }

        if ($startsBeforeDateTime != null) {
            $qb = $this->filterByStartsAtBefore($startsBeforeDateTime, $qb);
        }

        if ($movieTitle != null) {
            $qb = $this->filterByMovieTitle($movieTitle, $qb);
        }

        if ($isPublished != null) {
            $qb = $this->filterByIsPublished($isPublished, $qb);
        }

        return $qb;
    }

    public function findByCinemaAtDate(string $date, Cinema $cinema,): array
    {
        $qb = $this->createQueryBuilder('s')
            ->addSelect("sr.name AS screeningRoomName")
            ->innerJoin("s.screeningRoom", "sr");

        $qb = $this->filterByStartsAtDate($date, $qb);

        $qb = $this->filterByCinema($cinema, $qb);

        return $qb->getQuery()->getResult();
    }

    public function findByCinemaAndScreeningRoomAtDate(
        string|DateTimeImmutable $date,
        Cinema $cinema,
        ScreeningRoom $screeningRoom
    ): array {
        $qb = $this->filterByCinema($cinema);

        $qb = $this->filterByStartsAtDate($date, $qb);

        $qb = $this->filterByScreeningRoom($screeningRoom, $qb);

        return $qb->getQuery()->getResult();
    }

    public function findUnpublishedByCinemaAtDate(
        Cinema $cinema,
        string $date
    ) {
        $qb = $this->filterByCinema($cinema);
        $qb = $this->filterByStartsAtDate($date, $qb);

        return $qb->getQuery()->getResult();
    }


    public function findShowtimesByCinemaAndIsPublished(Cinema $cinema, bool $isPublished = false): array
    {
        $qb = $this->createQueryBuilder("s");

        $qb = $this->filterByCinema($cinema, $qb);

        $qb = $this->filterByIsPublished($isPublished, $qb);

        return $qb->getQuery()
            ->getResult();
    }


    public function findDistinctMovies(Cinema $cinema, bool $isPublished = true)
    {
        $qb = $this->createQueryBuilder("s")
            ->innerJoin("s.movieScreeningFormat", "mf")
            ->innerJoin("mf.movie", "m")
            ->innerJoin("s.screeningRoom", "sr")
            ->select("m.id, m.title, m.durationInMinutes")
            ->distinct();

        $qb = $this->filterByCinema($cinema, $qb);

        $qb = $this->filterByIsPublished($isPublished, $qb);

        return $qb->getQuery()->getResult();
    }


    public function findMovieIdsForPublishedShowtimes(
        Cinema $cinema,
        \DateTimeImmutable $startsFrom,
        string $endsAt,
        string $cinemaOpenHour,
        bool $isPublished = true
    ) {
        $qb = $this->createQueryBuilder("s")
            ->innerJoin("s.movieScreeningFormat", "mf")
            ->innerJoin("mf.movie", "m")
            ->innerJoin("s.screeningRoom", "sr")
            ->select("m.id")
            ->distinct()
            ->andWhere('DATE(s.startsAt) < :endsAt')
            ->andWhere("(s.startsAt > :startsFrom 
                            OR 
                        (DATE(s.startsAt) = DATE(:startsFrom) AND HOUR(s.startsAt) < :openHour))")
            ->setParameter('openHour', $cinemaOpenHour, Types::INTEGER)
            ->setParameter("startsFrom", $startsFrom)
            ->setParameter('endsAt', $endsAt);

        $qb = $this->filterByCinema($cinema, $qb);

        $qb = $this->filterByIsPublished($isPublished, $qb);

        return $qb->getQuery()->getSingleColumnResult();
    }



    public function findUpcomingShowtimesForTheMovie(
        Cinema $cinema,
        Movie $movie,
        string $cinemaOpenHour,
        \DateTimeImmutable $startsFrom,
        \DateTimeImmutable $endsAt,
    ) {
        $qb = $this->createQueryBuilder("s")
            ->select("s.id, s.startsAt, s.endsAt, s.slug, sr.name as screeningRoomName, vf.name as visualFormatName, sf.languagePresentation")
            ->innerJoin("s.movieScreeningFormat", "msf")
            ->innerJoin("msf.movie", "m")
            ->innerJoin("msf.screeningFormat", "sf")
            ->innerJoin("sf.visualFormat", "vf")
            ->innerJoin("s.screeningRoom", "sr")
            ->andWhere('DATE(s.startsAt) < :endsAt')
            ->andWhere("s.startsAt > :startsFrom OR HOUR(s.startsAt) < :openHour")
            ->setParameter('openHour', $cinemaOpenHour, Types::INTEGER)
            ->setParameter("startsFrom", $startsFrom)
            ->setParameter('endsAt', $endsAt);

        $qb = $this->filterByMovie($movie, $qb);
        $qb = $this->filterByCinema($cinema, $qb);
        $qb = $this->filterByIsPublished(true, $qb);

        return $qb->getQuery()->getResult();
    }


    public function isScheduledShowtimeForMovie(Cinema $cinema): array
    {
        $qb = $this->createQueryBuilder("s")
            ->select("DISTINCT m.id")
            ->innerJoin("s.movieScreeningFormat", "msf")
            ->innerJoin("msf.movie", "m");

        $qb = $this->filterByCinema($cinema, $qb);

        return $qb->getQuery()->getSingleColumnResult();
    }

    public function findPublishedShowtimesByCinemaAndMovies(
        Cinema $cinema,
        array $movieIds,
        string $cinemaOpenHour,
        \DateTimeImmutable $startFrom,
        bool $isPublished = true
    ) {
        $qb = $this->createQueryBuilder("s")
            ->andWhere("s.startsAt > :startFrom OR HOUR(s.startsAt) < :openHour")
            ->setParameter('startFrom', $startFrom)
            ->setParameter('openHour', $cinemaOpenHour);

        $qb = $this->filterByStartsAtDate($startFrom, $qb);
        $qb = $this->filterByCinema($cinema, $qb);
        $qb = $this->filterByIsPublished($isPublished, $qb);
        $qb = $this->filterByMovieIds($movieIds, $qb);

        return $qb->getQuery()->getResult();
    }


    public function filterByMovieTitle(string $movieTitle, ?QueryBuilder $qb = null): QueryBuilder
    {
        return ($qb ?? $this->createQueryBuilder("s"))
            ->innerJoin("s.movieScreeningFormat", "mf")
            ->innerJoin("mf.movie", "m")
            ->andWhere("LOWER(m.title) LIKE LOWER(:movieTitle)")
            ->setParameter("movieTitle", "%" . $movieTitle . "%");
    }

    public function filterByStartsAtAfter(\DateTimeImmutable $startsAt, ?QueryBuilder $qb = null): QueryBuilder
    {
        return ($qb ?? $this->createQueryBuilder("s"))
            ->andWhere("s.startsAt >= :startsAt")
            ->setParameter("startsAt", $startsAt);
    }

    public function filterByStartsAtBefore(\DateTimeImmutable $endsAt, ?QueryBuilder $qb = null): QueryBuilder
    {
        return ($qb ?? $this->createQueryBuilder("s"))
            ->andWhere("s.startsAt <= :endsAt")
            ->setParameter("endsAt", $endsAt);
    }

    public function filterByStartsAtDate(string|DateTimeImmutable $date, ?QueryBuilder $qb = null): QueryBuilder
    {
        return ($qb ?? $this->createQueryBuilder("s"))
            ->andWhere("DATE(s.startsAt) = DATE(:date)")
            ->setParameter("date", $date);
    }

    public function filterByScreeningRoom(ScreeningRoom $screeningRoom, ?QueryBuilder $qb = null): QueryBuilder
    {
        return ($qb ?? $this->createQueryBuilder("s"))
            ->andWhere("s.screeningRoom = :screeningRoom")
            ->setParameter("screeningRoom", $screeningRoom);
    }

    public function filterByScreeningRoomName(string $roomName, ?QueryBuilder $qb = null): QueryBuilder
    {
        return ($qb ?? $this->createQueryBuilder("s"))
            ->innerJoin("s.screeningRoom", "sr")
            ->andWhere("sr.name = :roomName")
            ->setParameter("roomName", $roomName);
    }


    public function filterByCinema(Cinema $cinema, ?QueryBuilder $qb = null): QueryBuilder
    {
        return ($qb ?? $this->createQueryBuilder("s"))
            ->andWhere("s.cinema = :cinema")
            ->setParameter("cinema", $cinema);
    }

    public function filterByIsPublished(bool $isPublished, ?QueryBuilder $qb = null): QueryBuilder
    {
        return ($qb ?? $this->createQueryBuilder("s"))
            ->andWhere("s.isPublished = :isPublished")
            ->setParameter("isPublished", $isPublished);
    }

    public function filterByMovie(Movie $movie, ?QueryBuilder $qb = null): QueryBuilder
    {
        return ($qb ?? $this->createQueryBuilder("s"))
            ->innerJoin("s.movieScreeningFormat", "mf")
            ->andWhere("mf.movie = :movie")
            ->setParameter("movie", $movie);
    }

    public function filterByMovieIds($movieIds, ?QueryBuilder $qb = null): QueryBuilder
    {
        return ($qb ?? $this->createQueryBuilder("s"))
            ->innerJoin("s.movieScreeningFormat", "mf")
            ->innerJoin("mf.movie", "m")
            ->andWhere("m.id IN (:movieIds)")
            ->setParameter("movieIds", $movieIds);
    }


    // public function findShowtimesInRangeForMovie(
    //     Cinema $cinema,
    //     Movie $movie,
    //     \DateTimeImmutable $startDate,
    //     \DateTimeImmutable $endDate,
    // ) {
    //     $qb = $this->createQueryBuilder("s")
    //         ->select("s.id, s.startsAt, s.endsAt, s.slug, sr.name as screeningRoomName, vf.name as visualFormatName, sf.languagePresentation")
    //         ->innerJoin("s.movieScreeningFormat", "msf")
    //         ->innerJoin("msf.movie", "m")
    //         ->innerJoin("msf.screeningFormat", "sf")
    //         ->innerJoin("sf.visualFormat", "vf")
    //         ->innerJoin("s.screeningRoom", "sr")
    //         ->andWhere("s.cinema = :cinema")
    //         ->addOrderBy("s.startsAt", "ASC")
    //         ->setParameter("cinema", $cinema);

    //     $qb = $this->filterByAfterStartsAt($startDate, $qb);
    //     $qb = $this->filterByBeforeStartsAt($endDate, $qb);
    //     $qb = $this->filterByMovie($movie, $qb);
    //     $qb = $this->filterByIsPublished(true, $qb);

    //     return $qb->getQuery()->getResult();
    // }



}
