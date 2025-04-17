<?php

namespace App\Repository;

use App\Entity\Cinema;
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
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Showtime::class);
    }

    public function findOverlapping(
        Cinema $cinema,
        \DateTimeImmutable $startsAt,
        \DateTimeImmutable $endsAt,
        ?int $excludeId = null
    ): QueryBuilder {
        $qb = $this->createQueryBuilder('s')
            ->andWhere('(
                (:startsAt >= s.startsAt AND :startsAt < s.endsAt) OR
                (:endsAt > s.startsAt AND :endsAt <= s.endsAt) OR
                (:startsAt <= s.startsAt AND :endsAt >= s.endsAt)
            )')
            ->andWhere("s.cinema = :cinema")
            ->setParameter('startsAt', $startsAt)
            ->setParameter('endsAt', $endsAt)
            ->setParameter('cinema', $cinema);

        if ($excludeId) {
            $qb->andWhere('s.id != :excludeId')
                ->setParameter('excludeId', $excludeId);
        }
        return $qb;
    }


    /**
     * @return Showtime[] returns showtimes overlapping in the same room
     */
    public function findOverlappingForRoom(
        Cinema $cinema,
        ScreeningRoom $screeningRoom,
        \DateTimeImmutable $startsAt,
        \DateTimeImmutable $endsAt,
        ?int $excludeId = null
    ): array {
        return $this->findOverlapping($cinema, $startsAt, $endsAt, $excludeId)
            ->andWhere('s.screeningRoom = :screeningRoom')
            ->setParameter("screeningRoom", $screeningRoom)
            ->getQuery()
            ->getResult();
    }

    /**
     * @return Showtime[] returns showtimes where samve movie was scheduled in different room
     */
    public function findOverlappingForMovie(
        Cinema $cinema,
        MovieScreeningFormat $movieScreeningFormat,
        \DateTimeImmutable $startsAt,
        \DateTimeImmutable $endsAt,
        ?int $excludeId = null
    ): ?Showtime {
        return $this->findOverlapping($cinema, $startsAt, $endsAt, $excludeId)
            ->andWhere('s.movieScreeningFormat = :movieScreeningFormat')
            ->setParameter("movieScreeningFormat", $movieScreeningFormat)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * @return Showtime[] Returns showtimes where same movie was scheduled in different rooms
     */
    public function findOverlappingForMovieMultiple(
        Cinema $cinema,
        MovieScreeningFormat $movieScreeningFormat,
        \DateTimeImmutable $startsAt,
        \DateTimeImmutable $endsAt,
        ?int $excludeId = null
    ): array {
        return $this->findOverlapping($cinema, $startsAt, $endsAt, $excludeId)
            ->andWhere('s.movieScreeningFormat = :movieScreeningFormat')
            ->setParameter("movieScreeningFormat", $movieScreeningFormat)
            ->getQuery()
            ->getResult();
    }

    /**
     * @return Showtime[] returns filtered showtimes
     */
    public function findFiltered(
        Cinema $cinema,
        ?ScreeningRoom $screeningRoom = null,
        ?string $showtimeStartTime = null,
        ?string $showtimeEndTime = null,
        ?string $movieTitle = null,
        bool $isPublished = false,
        ?bool $includeRoomSlug = false
    ): array {

        $qb = $this->createQueryBuilder('s')
            ->addOrderBy("s.startsAt", "ASC")
            ->andWhere("s.cinema = :cinema")
            ->setParameter("cinema", $cinema);

        if ($includeRoomSlug) {
            $qb->addSelect("sr.slug AS screeningRoomSlug")
                ->innerJoin("s.screeningRoom", "sr");
        }

        if ($screeningRoom) {
            $qb = $this->findByScreeningRoomName($screeningRoom, $qb);
        }

        if ($showtimeStartTime) {
            $qb = $this->findByStartingFrom($showtimeStartTime, $qb);
        }

        if ($showtimeEndTime) {
            $qb = $this->findByStartingBefore($showtimeEndTime, $qb);
        }

        if ($movieTitle) {
            $qb = $this->findByMovieTitle($movieTitle, $qb);
        }

        if ($isPublished) {
            $qb = $this->findPublished($isPublished, $qb);
        }

        return $qb->getQuery()
            ->getResult();
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
            ->andWhere("m.title LIKE :movieTitle")
            ->setParameter("movieTitle", "%" . $movieTitle . "%");
    }

    public function findByStartingFrom(string $startsAt, ?QueryBuilder $qb = null): QueryBuilder
    {
        return ($qb ?? $this->createQueryBuilder("s"))
            ->andWhere("s.startsAt >= :startsAt")
            ->setParameter("startsAt", $startsAt, Types::STRING);
    }

    public function findByStartingBefore(string $endsAt, ?QueryBuilder $qb = null): QueryBuilder
    {
        return ($qb ?? $this->createQueryBuilder("s"))
            ->andWhere("s.endsAt <= :showtimeEndTime")
            ->setParameter("showtimeEndTime", $endsAt, Types::STRING);
    }

    public function findByScreeningRoomName(ScreeningRoom $screeningRoom, ?QueryBuilder $qb = null): QueryBuilder
    {
        return ($qb ?? $this->createQueryBuilder("s"))
            ->andWhere("s.screeningRoom = :screeningRoom")
            ->setParameter("screeningRoom", $screeningRoom);
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

    public function findMovieIdsForPublishedShowtimes(Cinema $cinema, bool $isPublished = true)
    {
        return $this->createQueryBuilder("s")
            ->innerJoin("s.movieScreeningFormat", "mf")
            ->innerJoin("mf.movie", "m")
            ->innerJoin("s.screeningRoom", "sr")
            ->select("m.id")
            ->distinct()
            ->andWhere("s.isPublished = :isPublished")
            ->andWhere("sr.cinema = :cinema")
            ->setParameter("cinema", $cinema)
            ->setParameter("isPublished", $isPublished)
            ->getQuery()
            ->getSingleColumnResult()
        ;
    }


    /**
     * @return Showtime[]
     */
    public function findScheduledShowtimesForMovieBetweenDates(
        int $movieId,
        \DateTimeImmutable $startDate,
        \DateTimeImmutable $endDate,
        bool $isPublished = true
    ): array {
        return $this->createQueryBuilder("s")
            ->innerJoin("s.movieScreeningFormat", "msf")
            ->innerJoin("msf.movie", "m")
            ->addSelect("msf")
            ->andWhere("s.isPublished = :isPublished")
            ->andWhere("m.id = :id")
            ->andWhere("s.startsAt > :startDate")
            ->andWhere("s.endsAt < :endDate")
            ->setParameter("startDate", $startDate)
            ->setParameter("endDate", $endDate)
            ->setParameter("id", $movieId)
            ->setParameter("isPublished", $isPublished)
            ->getQuery()
            ->getResult()
        ;
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
}
