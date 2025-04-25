<?php

namespace App\Repository;

use App\Entity\Cinema;
use App\Entity\Movie;
use App\Entity\MovieScreeningFormat;
use App\Entity\ScreeningRoom;
use App\Entity\Showtime;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\DBAL\Types\Type;
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
            ->andWhere('DATE(s.startsAt) = :date')
            ->andWhere("(
                (:startsAt >= s.startsAt AND :startsAt < s.endsAt) OR
                (:endsAt > s.startsAt AND :endsAt <= s.endsAt) OR
                (:startsAt <= s.startsAt AND :endsAt >= s.endsAt)
            )")
            ->andWhere("s.cinema = :cinema")
            ->setParameter('date', $date)
            ->setParameter('startsAt', $startsAt)
            ->setParameter('endsAt', $endsAt)
            ->setParameter('cinema', $cinema);

        if ($excludeShowtime) {
            $qb->andWhere('s != :excludeShowtime')
                ->setParameter('excludeShowtime', $excludeShowtime);
        }
        return $qb;
    }


    /**
     * @return Showtime[] returns showtimes overlapping in the same room
     */
    public function findOverlappingForRoom(
        ScreeningRoom $screeningRoom,
        Cinema $cinema,
        string $date,
        \DateTimeImmutable $startsAt,
        \DateTimeImmutable $endsAt,
        ?Showtime $excludeShowtime = null,
    ): array {
        return $this->findOverlapping($cinema, $date, $startsAt, $endsAt, $excludeShowtime)
            ->andWhere('s.screeningRoom = :screeningRoom')
            ->setParameter("screeningRoom", $screeningRoom)
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


    /**
     * @return Showtime[]|QueryBuilder returns filtered showtimes or query builder
     */
    public function findFiltered(
        Cinema $cinema,
        ?ScreeningRoom $screeningRoom = null,
        string|\DateTimeImmutable|null $showtimeStartTime = null,
        string|\DateTimeImmutable|null $showtimeEndTime = null,
        ?string $movieTitle = null,
        ?bool $isPublished = null,
        bool $includeScreeningRoomName = false,
        ?string $date = null,
        ?array $movieIds = null,
        bool $returnQueryBuilder = false,
        ?Movie $movie = null,
    ): array|QueryBuilder {

        $qb = $this->createQueryBuilder('s')
            ->addOrderBy("s.startsAt", "ASC")
            ->andWhere("s.cinema = :cinema")
            ->setParameter("cinema", $cinema);

        if ($includeScreeningRoomName) {
            $qb->addSelect("sr.name AS screeningRoomName")
                ->innerJoin("s.screeningRoom", "sr");
        }

        if ($screeningRoom !== null) {
            $qb = $this->findByScreeningRoomName($screeningRoom, $qb);
        }

        if (!empty($showtimeStartTime)) {
            $qb->andWhere('s.startsAt >= :startDate')
                ->setParameter('startDate', $showtimeStartTime);
        }

        if (!empty($showtimeEndTime)) {
            $qb->andWhere('s.startsAt <= :endDate')
                ->setParameter('endDate', $showtimeEndTime);
        }

        if ($date !== null) {
            $qb = $this->findForDate($date, $qb);
        }

        if ($movieTitle) {
            $qb = $this->findByMovieTitle($movieTitle, $qb);
        }

        if ($isPublished !== null) {
            $qb = $this->findPublished($isPublished, $qb);
        }

        if ($movieIds !== null) {
            $qb = $this->findByMovieIds($movieIds, $qb);
        }

        if ($movie !== null) {
            $qb = $this->findByMovie($movie, $qb);
        }

        if ($returnQueryBuilder) {
            return $qb;
        }


        return $qb->getQuery()
            ->getResult();
    }


    public function findShowtimesByCinemaAndIsPublished(Cinema $cinema, bool $isPublished = false): array
    {
        return $this->createQueryBuilder("s")
            ->andWhere("cinema = :cinema")
            ->andWhere("s.isPublished = :isPublished")
            ->setParameter("isPublished", $isPublished)
            ->setParameter("cinema", $cinema)
            ->getQuery()
            ->getResult();
    }


    public function findByMovie(Movie $movie, ?QueryBuilder $qb = null): QueryBuilder
    {
        return ($qb ?? $this->createQueryBuilder("s"))
            ->innerJoin("s.movieScreeningFormat", "mf")
            ->andWhere("mf.movie = :movie")
            ->setParameter("movie", $movie);
    }


    public function findPublished(bool $isPublished, ?QueryBuilder $qb = null): QueryBuilder
    {
        return ($qb ?? $this->createQueryBuilder("s"))
            ->andWhere("s.isPublished = :isPublished")
            ->setParameter("isPublished", $isPublished);
    }

    public function findByMovieTitle(string $movieTitle, ?QueryBuilder $qb = null): QueryBuilder
    {
        return ($qb ?? $this->createQueryBuilder("s"))
            ->innerJoin("s.movieScreeningFormat", "mf")
            ->innerJoin("mf.movie", "m")
            ->andWhere("LOWER(m.title) LIKE LOWER(:movieTitle)")
            ->setParameter("movieTitle", "%" . $movieTitle . "%");
    }

    public function findByMovieIds($movieIds, ?QueryBuilder $qb = null): QueryBuilder
    {
        return ($qb ?? $this->createQueryBuilder("s"))
            ->innerJoin("s.movieScreeningFormat", "mf")
            ->innerJoin("mf.movie", "m")
            ->andWhere("m.id IN (:movieIds)")
            ->setParameter("movieIds", $movieIds);
    }

    public function findByStartingFrom(string|\DateTimeImmutable $startsAt, ?QueryBuilder $qb = null): QueryBuilder
    {
        return ($qb ?? $this->createQueryBuilder("s"))
            ->andWhere("s.startsAt >= :startsAt")
            ->setParameter("startsAt", $startsAt);
    }

    public function findByStartingBefore(string|\DateTimeImmutable $endsAt, ?QueryBuilder $qb = null): QueryBuilder
    {
        return ($qb ?? $this->createQueryBuilder("s"))
            ->andWhere("s.startsAt <= :endsAt")
            ->setParameter("endsAt", $endsAt);
    }

    public function findForDate(string $date, ?QueryBuilder $qb = null): QueryBuilder
    {
        return ($qb ?? $this->createQueryBuilder("s"))
            ->andWhere("DATE(s.startsAt) = :date")
            ->setParameter("date", $date);
    }

    public function findByScreeningRoomName(ScreeningRoom $screeningRoom, ?QueryBuilder $qb = null): QueryBuilder
    {
        return ($qb ?? $this->createQueryBuilder("s"))
            ->andWhere("s.screeningRoom = :screeningRoom")
            ->setParameter("screeningRoom", $screeningRoom);
    }

    public function findByCinema(Cinema $cinema, ?QueryBuilder $qb = null): QueryBuilder
    {
        return ($qb ?? $this->createQueryBuilder("s"))
            ->andWhere("s.cinema = :cinema")
            ->setParameter("cinema", $cinema);
    }



    public function findDistinctMovies(Cinema $cinema, bool $isPublished = true)
    {
        return $this->createQueryBuilder("s")
            ->innerJoin("s.movieScreeningFormat", "mf")
            ->innerJoin("mf.movie", "m")
            ->innerJoin("s.screeningRoom", "sr")
            ->select("m.id, m.title, m.durationInMinutes")
            ->distinct()
            ->andWhere("s.isPublished = :isPublished")
            ->andWhere("sr.cinema = :cinema")
            ->setParameter("cinema", $cinema)
            ->setParameter("isPublished", $isPublished)
            ->getQuery()
            ->getResult()
        ;
    }


    public function findMovieIdsForPublishedShowtimes(
        Cinema $cinema,
        \DateTimeImmutable $startsFrom,
        string $endsAt,
        string $cinemaOpenHour,
        bool $isPublished = true)
    {
        return $this->createQueryBuilder("s")
            ->innerJoin("s.movieScreeningFormat", "mf")
            ->innerJoin("mf.movie", "m")
            ->innerJoin("s.screeningRoom", "sr")
            ->select("m.id")
            ->distinct()
            ->andWhere("s.isPublished = :isPublished")
            ->andWhere("sr.cinema = :cinema")
            ->andWhere('DATE(s.startsAt) < :endsAt')
            ->andWhere("s.startsAt > :startsFrom OR HOUR(s.startsAt) < :openHour")
            ->setParameter('openHour', $cinemaOpenHour, Types::INTEGER)
            ->setParameter("startsFrom", $startsFrom)
            ->setParameter("cinema", $cinema)
            ->setParameter("isPublished", $isPublished)
            ->setParameter('endsAt', $endsAt)
            ->getQuery()
            ->getSingleColumnResult()
        ;
    }

    public function findPublishedShowtimesForDate(
        Cinema $cinema,
        array $movieIds,
        string $cinemaOpenHour,
        \DateTimeImmutable $startFrom
    ) {
        $qb = $this->createQueryBuilder("s")
            ->andWhere('DATE(s.startsAt) = DATE(:startFrom)')
            ->andWhere("s.startsAt > :startFrom OR HOUR(s.startsAt) < :openHour")
            ->setParameter('startFrom', $startFrom)
            ->setParameter('openHour', $cinemaOpenHour)
            ;

        $qb = $this->findByCinema($cinema, $qb);
        $qb = $this->findPublished(true, $qb);
        $qb = $this->findByMovieIds($movieIds, $qb);

        return $qb->getQuery()->getResult();
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

        $qb = $this->findByMovie($movie, $qb);
        $qb = $this->findByCinema($cinema, $qb);
        $qb = $this->findPublished(true, $qb);

        return $qb->getQuery()->getResult();
    }


    public function isScheduledShowtimeForMovie(Cinema $cinema): array
    {
        return $this->createQueryBuilder("s")
            ->select("DISTINCT m.id")
            ->innerJoin("s.movieScreeningFormat", "msf")
            ->innerJoin("msf.movie", "m")
            ->where("msf.cinema = :cinema")
            ->setParameter("cinema", $cinema)
            ->getQuery()
            ->getSingleColumnResult();
    }


    public function findShowtimesInRangeForMovie(
        Cinema $cinema,
        Movie $movie,
        \DateTimeImmutable $startDate,
        \DateTimeImmutable $endDate,
    ) {
        $qb = $this->createQueryBuilder("s")
            ->select("s.id, s.startsAt, s.endsAt, s.slug, sr.name as screeningRoomName, vf.name as visualFormatName, sf.languagePresentation")
            ->innerJoin("s.movieScreeningFormat", "msf")
            ->innerJoin("msf.movie", "m")
            ->innerJoin("msf.screeningFormat", "sf")
            ->innerJoin("sf.visualFormat", "vf")
            ->innerJoin("s.screeningRoom", "sr")
            ->andWhere("s.cinema = :cinema")
            ->addOrderBy("s.startsAt", "ASC")
            ->setParameter("cinema", $cinema);

        $qb = $this->findByStartingFrom($startDate, $qb);
        $qb = $this->findByStartingBefore($endDate, $qb);
        $qb = $this->findByMovie($movie, $qb);
        $qb = $this->findPublished(true, $qb);

        return $qb->getQuery()->getResult();
    }

    /**
     * Find unpublished showtimes for a specific cinema on a given date.
     * 
     * @return Showtime[]
     */
    public function findUnpublishedShowtimesByDate(Cinema $cinema, string $date, bool $isPublished = false): array
    {

        return $this->createQueryBuilder("s")
            ->andWhere("s.cinema = :cinema")
            ->andWhere("s.isPublished = :isPublished")
            ->andWhere("DATE(s.startsAt) = :date")
            ->setParameter("date", $date)
            ->setParameter("isPublished", $isPublished)
            ->setParameter("cinema", $cinema)
            ->addOrderBy("s.startsAt", "ASC")
            ->getQuery()
            ->getResult();
    }
}
