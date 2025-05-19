<?php

namespace App\Repository;

use App\Entity\ReservationSeat;
use App\Entity\Showtime;
use DateTimeImmutable;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ReservationSeat>
 */
class ReservationSeatRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ReservationSeat::class);
    }


    public function findSeatsByShowtime(Showtime $showtime)
    {
        $qb = $this->createQueryBuilder('rs')
            ->addSelect("srs")
            ->innerJoin("rs.seat", "srs")
            ->addSelect("s")
            ->innerJoin("srs.seat", "s")
            ->addOrderBy("s.rowNum", "ASC")
            ->addOrderBy("s.seatNumInRow", "ASC");

        $qb = $this->filterByShowtime($showtime, $qb);

        return $qb->getQuery()->getResult();
    }


    public function findExpiredLockedSeats(
        DateTimeImmutable $currentDateTime,
        ?string $status = "locked"
    ) {
        return $this->createQueryBuilder("rs")
            ->andWhere("rs.statusLockedExpiresAt < :currentDateTime")
            ->andWhere("rs.status = :status")
            ->setParameter("currentDateTime", $currentDateTime)
            ->setParameter("status", $status)
            ->getQuery()
            ->getResult()
        ;
    }

    public function filterByShowtime(Showtime $showtime, ?QueryBuilder $qb): QueryBuilder
    {
        return ($qb ?? $this->createQueryBuilder('rs'))
            ->andWhere("rs.showtime = :showtime")
            ->setParameter('showtime', $showtime);
    }


    public function findDistinctPriceTiersByShowtime(Showtime $showtime) 
    {
        $query = $this->getEntityManager()->createQuery(
            'SELECT DISTINCT rs.priceTierType, rs.priceTierPrice, rs.priceTierColor 
             FROM App\Entity\ReservationSeat rs
             WHERE rs.showtime = :showtime
             AND rs.priceTierType IS NOT NULL
             AND rs.priceTierPrice IS NOT NULL
             AND rs.priceTierColor IS NOT NULL
             ORDER BY rs.priceTierPrice ASC'
        );
        
        $query->setParameter('showtime', $showtime);
        
        return $query->getResult();
    }

}
