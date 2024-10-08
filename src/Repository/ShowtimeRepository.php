<?php

namespace App\Repository;

use App\Entity\Cinema;
use App\Entity\MovieMovieType;
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
        \DateTimeInterface $startTime, 
        \DateTimeInterface $endTime, 
        ?int $excludeId = null)
    {
        $qb = $this->createQueryBuilder('s')
            ->orWhere('(:startTime >= s.startTime AND :startTime < s.endTime)')
            ->orWhere('(:endTime > s.startTime AND :endTime <= s.endTime)')
            ->orWhere('(:startTime <= s.startTime AND :endTime >= s.endTime)')
            ->andWhere("s.cinema = :cinema")
            ->setParameter('startTime', $startTime)
            ->setParameter('endTime', $endTime)
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
        \DateTimeInterface $startTime,
        \DateTimeInterface $endTime,
        ?int $excludeId = null
    ) {
        return $this->findOverlapping($cinema, $startTime, $endTime, $excludeId)
            ->andWhere('s.screeningRoom = :screeningRoom')
            ->setParameter("screeningRoom", $screeningRoom)
            ->getQuery()
            ->getResult();
    }

    public function findOverlappingForMovie(
        Cinema $cinema,
        MovieMovieType $movieFormat,
        \DateTimeInterface $startTime,
        \DateTimeInterface $endTime,
        ?int $excludeId = null
    ) {
        return $this->findOverlapping($cinema, $startTime, $endTime, $excludeId)
            ->andWhere('s.movieFormat = :movieFormat')
            ->setParameter("movieFormat", $movieFormat)
            ->getQuery()
            ->getResult();

    }

    public function findFiltered(
        Cinema $cinema,
        ?string $screeningRoomName = null,
        ?string $showtimeStartTime = null, 
        ?string $showtimeEndTime = null, 
        ?string $movieTitle = null) {

        $qb = $this->createQueryBuilder('s')
                ->addOrderBy("s.startTime")
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
                    ->andWhere("s.startTime >= :startsAt")
                    ->setParameter("startsAt", $startsAt);
    }

    public function findByStartingBefore(string|DateTimeInterface $endsAt, ?QueryBuilder $qb = null) {
        if (date('Y-m-d', strtotime($endsAt)) == $endsAt) {
            $endsAt = new \DateTime($endsAt);
            $endsAt->setTime(23, 59, 59);  
        }

        return ($qb ?? $this->createQueryBuilder("s"))
                        ->andWhere("s.endTime <= :showtimeEndTime")
                        ->setParameter("showtimeEndTime", $endsAt);

    }

    public function findByScreeningRoomName(string $screeningRoomName, ?QueryBuilder $qb = null) {
        
        return ($qb ?? $this->createQueryBuilder("s"))
                        ->innerJoin("s.screeningRoom", "sr")
                        ->andWhere("sr.name = :screeningRoomName")
                        ->setParameter("screeningRoomName", $screeningRoomName);
        
    }


    public function checkOne() {
        return $this->createQueryBuilder("s")
                        ->getQuery()->getResult();
    }

    public function dummyQuery() {
        return false;
    }


}
