<?php

namespace App\Repository;

use App\Contracts\SeatsGridInterface;
use App\Entity\ReservationSeat;
use App\Entity\ScreeningRoom;
use App\Entity\Showtime;
use DateTimeImmutable;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ReservationSeat>
 */
class ReservationSeatRepository extends ServiceEntityRepository implements SeatsGridInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ReservationSeat::class);
    }

    // also creating where clause for single filtering condition
    // seems to be redundant, cause same can be achieved
    // using findBy built int method
    // however it can be still useful to use to remove n + 1 problem

    // for the showtime
    public function findExpiredLockedSeats(
        DateTimeImmutable $currentDateTime,
        ?string $status = "locked") {
        return $this->createQueryBuilder("rs")
                    ->andWhere("rs.statusLockedExpiresAt < :currentDateTime")
                    ->andWhere("rs.status = :status")
                    ->setParameter("currentDateTime", $currentDateTime)
                    ->setParameter("status", $status)
                    ->getQuery()
                    ->getResult()

        ;
    }



    /**
     * @return int[]
     */
    public function findRows(ScreeningRoom $screeningRoom): array
    {
        $result = $this->createQueryBuilder('rs')
            ->select("DISTINCT s.rowNum")
            ->innerJoin("rs.showtime", "sh")
            ->innerJoin("sh.screeningRoom", "sr")
            ->innerJoin("sr.screeningRoomSeats", "srs")
            ->innerJoin("srs.seat", "cs")
            ->innerJoin("cs.seat", "s")
            ->andWhere("sr = :screeningRoom")
            ->setParameter("screeningRoom", $screeningRoom)
            ->addOrderBy("s.rowNum", "ASC")
            ->getQuery()
            ->getResult();
     
        return array_map("intval", array_column($result, "rowNum"));
    }


    /**
     * @return ReservationSeat[]
     */
    public function findSeatsInRow(
        ScreeningRoom $screeningRoom, 
        int $rowNum, 
        ?Showtime $showtime = null): array
    {
        return $this->createQueryBuilder('rs')
            ->innerJoin("rs.showtime", "sh")
            ->innerJoin("sh.screeningRoom", "sr")
            ->innerJoin("rs.seat", "srs")
            ->innerJoin("srs.seat", "cs")
            ->innerJoin("cs.seat", "s")
            ->andWhere("rs.showtime = :showtime")
            ->andWhere("sr = :screeningRoom")
            ->andWhere("s.rowNum = :rowNum")
            ->addOrderBy("s.rowNum", "ASC")
            ->addOrderBy("s.colNum", "ASC")
            ->setParameter("rowNum", $rowNum)
            ->setParameter("screeningRoom", $screeningRoom)
            ->setParameter("showtime", $showtime)
            ->getQuery()
            ->getResult()
            ;
        
    }


    

}
