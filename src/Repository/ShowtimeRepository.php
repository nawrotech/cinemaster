<?php

namespace App\Repository;

use App\Entity\Cinema;
use App\Entity\MovieFormat;
use App\Entity\ScreeningRoom;
use App\Entity\Showtime;
use DateTimeInterface;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
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
        \DateTimeInterface $startsAt, 
        \DateTimeInterface $endsAt, 
        ?int $excludeId = null): QueryBuilder
    {
        $qb = $this->createQueryBuilder('s')
            ->orWhere('(:startsAt >= s.startsAt AND :startsAt < s.endsAt)')
            ->orWhere('(:endsAt > s.startsAt AND :endsAt <= s.endsAt)')
            ->orWhere('(:startsAt <= s.startsAt AND :endsAt >= s.endsAt)')
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

    public function findOverlappingForRoom(
        Cinema $cinema,
        ScreeningRoom $screeningRoom,
        \DateTimeInterface $startsAt,
        \DateTimeInterface $endsAt,
        ?int $excludeId = null
    ): array {
        return $this->findOverlapping($cinema, $startsAt, $endsAt, $excludeId)
            ->andWhere('s.screeningRoom = :screeningRoom')
            ->setParameter("screeningRoom", $screeningRoom)
            ->getQuery()
            ->getResult();
    }

    public function findOverlappingForMovie(
        Cinema $cinema,
        MovieFormat $movieFormat,
        \DateTimeInterface $startsAt,
        \DateTimeInterface $endsAt,
        ?int $excludeId = null
    ): ?Showtime {
        return $this->findOverlapping($cinema, $startsAt, $endsAt, $excludeId)
            ->andWhere('s.movieFormat = :movieFormat')
            ->setParameter("movieFormat", $movieFormat)
            ->getQuery()
            ->getOneOrNullResult();

    }

    public function findFiltered(
        Cinema $cinema,
        ?string $screeningRoomName = null,
        ?string $showtimeStartTime = null, 
        ?string $showtimeEndTime = null, 
        ?string $movieTitle = null) {

        $qb = $this->createQueryBuilder('s')
                ->addOrderBy("s.startsAt")
                ->andWhere("s.cinema = :cinema")
                ->setParameter("cinema", $cinema);

        if ($screeningRoomName) {
            $qb = $this->findByScreeningRoomName($screeningRoomName, $qb);
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

        return $qb->getQuery()
                    ->getResult();
    }

    public function findByMovieTitle(string $movieTitle, ?QueryBuilder $qb = null) {
        return ($qb ?? $this->createQueryBuilder("s"))
                        ->innerJoin("s.movieFormat", "mf")
                        ->innerJoin("mf.movie", "m")
                        ->andWhere("m.title LIKE :movieTitle")
                        ->setParameter("movieTitle", "%{$movieTitle}%");
    }

    public function findByStartingFrom(string|DateTimeInterface $startsAt, ?QueryBuilder $qb = null) {
        if ($startsAt instanceof DateTimeInterface) {
            return $startsAt;
        }

        if (date('Y-m-d', strtotime($startsAt)) == $startsAt) {
            $startsAt = new \DateTime($startsAt);
            $startsAt->setTime(0, 0, 0);
        }
      
        return ($qb ?? $this->createQueryBuilder("s"))
                    ->andWhere("s.startsAt >= :startsAt")
                    ->setParameter("startsAt", $startsAt);
    }

    public function findByStartingBefore(string|DateTimeInterface $endsAt, ?QueryBuilder $qb = null) {
        if (date('Y-m-d', strtotime($endsAt)) == $endsAt) {
            $endsAt = new \DateTime($endsAt);
            $endsAt->setTime(23, 59, 59);  
        }

        return ($qb ?? $this->createQueryBuilder("s"))
                        ->andWhere("s.endsAt <= :showtimeEndTime")
                        ->setParameter("showtimeEndTime", $endsAt);

    }

    public function findByScreeningRoomName(string $screeningRoomName, ?QueryBuilder $qb = null) {
        
        return ($qb ?? $this->createQueryBuilder("s"))
                        ->innerJoin("s.screeningRoom", "sr")
                        ->andWhere("sr.name = :screeningRoomName")
                        ->setParameter("screeningRoomName", $screeningRoomName);
        
    }

    public function findDistinctMovies(Cinema $cinema, bool $isPublished = true) {
        return $this->createQueryBuilder("s")
                ->innerJoin("s.movieFormat", "mf")
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


    public function findForMovie(int $id, Cinema $cinema, bool $isPublished = true) {
        return $this->createQueryBuilder("s")
                    ->innerJoin("s.movieFormat", "mf")
                    ->innerJoin("mf.movie", "m")
                    ->addSelect("mf")
                    ->andWhere("s.isPublished = :isPublished")
                    ->andWhere("m.id = :id")
                    ->andWhere("s.cinema = :cinema")
                    ->setParameter("cinema", $cinema)
                    ->setParameter("id", $id)
                    ->setParameter("isPublished", $isPublished)
                    ->getQuery()
                    ->getResult()
            ;
    }

   

}
