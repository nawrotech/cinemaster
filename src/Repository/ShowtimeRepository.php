<?php

namespace App\Repository;

use App\Entity\Movie;
use App\Entity\ScreeningRoom;
use App\Entity\Showtime;
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

    // public function findOverlapping(ScreeningRoom $screeningRoom, \DateTimeInterface $startTime, \DateTimeInterface $endTime, ?int $excludeId = null)
    public function findOverlapping(\DateTimeInterface $startTime, \DateTimeInterface $endTime, ?int $excludeId = null)
    {
        // validate what could be returned from this one or many movies
        $qb = $this->createQueryBuilder('s')
            ->orWhere('(:startTime >= s.startTime AND :startTime < s.endTime)')
            ->orWhere('(:endTime > s.startTime AND :endTime <= s.endTime)')
            ->orWhere('(:startTime <= s.startTime AND :endTime >= s.endTime)')
            // ->andWhere('s.screeningRoom = :screeningRoom')
            // ->setParameter('screeningRoom', $screeningRoom)
            ->setParameter('startTime', $startTime)
            ->setParameter('endTime', $endTime);

        if ($excludeId) {
            $qb->andWhere('s.id != :excludeId')
                ->setParameter('excludeId', $excludeId);
        }

        return $qb;

        // ->getQuery()->getResult();
    }

    public function findOverlappingForRoom(
        ScreeningRoom $screeningRoom,
        \DateTimeInterface $startTime,
        \DateTimeInterface $endTime,
        ?int $excludeId = null
    ) {
        return $this->findOverlapping($startTime, $endTime)
            ->andWhere('s.screeningRoom = :screeningRoom')
            ->setParameter("screeningRoom", $screeningRoom)
            ->getQuery()->getResult();
    }

    public function findOverlappingForMovie(
        Movie $movie,
        \DateTimeInterface $startTime,
        \DateTimeInterface $endTime,
        ?int $excludeId = null
    ) {
        return $this->findOverlapping($startTime, $endTime, $excludeId)
            ->innerJoin("s.movie", "m")
            ->addSelect("m")


            ->andWhere('s. = :screeningRoom');
    }
}
