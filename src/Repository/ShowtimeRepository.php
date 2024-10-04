<?php

namespace App\Repository;

use App\Entity\ScreeningRoom;
use App\Entity\Showtime;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
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

    public function findOverlapping(ScreeningRoom $screeningRoom, \DateTimeInterface $startTime, \DateTimeInterface $endTime, ?int $excludeId = null)
    {
        // validate what could be returned from this one or many movies
        $qb = $this->createQueryBuilder('s')
            ->orWhere('(:startTime >= s.startTime AND :startTime < s.endTime)')
            ->orWhere('(:endTime > s.startTime AND :endTime <= s.endTime)')
            ->orWhere('(:startTime <= s.startTime AND :endTime >= s.endTime)')
            ->andWhere('s.screeningRoom = :screeningRoom')
            ->setParameter('screeningRoom', $screeningRoom)
            ->setParameter('startTime', $startTime)
            ->setParameter('endTime', $endTime);

        // dd($qb->getQuery()->getSQL());
        if ($excludeId) {
            $qb->andWhere('s.id != :excludeId')
                ->setParameter('excludeId', $excludeId);
        }
        // chain it somehow so it checks for movies duplicates
        // return $qb->getQuery()->getOneOrNullResult();
        return $qb->getQuery()->getResult();
    }

    public function findOverlappingForMovie(ScreeningRoom $screeningRoom, \DateTimeInterface $startTime, \DateTimeInterface $endTime, ?int $excludeId = null) {}
}
