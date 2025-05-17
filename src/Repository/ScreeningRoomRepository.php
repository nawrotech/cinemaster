<?php

namespace App\Repository;

use App\Entity\Cinema;
use App\Entity\ScreeningRoom;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ScreeningRoom>
 */
class ScreeningRoomRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ScreeningRoom::class);
    }


    public function findDistinctRoomNames(Cinema $cinema): array
    {
        return $this->createQueryBuilder('sr')
                    ->select("sr.name")
                    ->distinct()
                    ->andWhere("sr.cinema = :cinema")
                    ->setParameter("cinema", $cinema)
                    ->getQuery()
                    ->getSingleColumnResult();
    }

    /**
     * @return ScreeningRoom[]
     */
    public function findByCinemaAndActiveStatus(Cinema $cinema, ?bool $isActive = null): array
    {
        $qb = $this->createQueryBuilder('sr')
            ->andWhere('sr.cinema = :cinema')
            ->setParameter('cinema', $cinema);

        if ($isActive !== null) {
            $qb->andWhere('sr.active = :active')
                ->setParameter('active', $isActive);
        }

        return $qb->getQuery()->getResult();
    
    }


    public function hasShowtimes(ScreeningRoom $screeningRoom): bool
    {
        return $this->createQueryBuilder('sr')
            ->select('1')
            ->join('sr.showtimes', 's')
            ->where('sr.id = :screeningRoomId')
            ->setParameter('screeningRoomId', $screeningRoom->getId())
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult() !== null;
    }

}
