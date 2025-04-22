<?php

namespace App\Repository;

use App\Entity\Reservation;
use App\Entity\Showtime;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Reservation>
 */
class ReservationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Reservation::class);
    }


    public function hasReservations(Showtime $showtime): bool
    {
        return $this->createQueryBuilder('r')
            ->select('1')
            ->where('r.showtime = :showtime')
            ->setParameter('showtime', $showtime)
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult() !== null;
    }


}
