<?php

namespace App\Repository;

use App\Entity\PriceTier;
use App\Entity\ScreeningRoom;
use App\Entity\ScreeningRoomSeat;
use App\Enum\ScreeningRoomSeatType;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ScreeningRoomSeat>
 */
class ScreeningRoomSeatRepository extends ServiceEntityRepository
{
    public function __construct(
        ManagerRegistry $registry,
    ) {
        parent::__construct($registry, ScreeningRoomSeat::class);
    }

    public function findSeatsByScreeningRoom(ScreeningRoom $screeningRoom)
    {
        return $this->createQueryBuilder('srs')
            ->addSelect("s")
            ->innerJoin("srs.seat", "s")
            ->andWhere("srs.screeningRoom = :screeningRoom")
            ->setParameter("screeningRoom", $screeningRoom)
            ->addOrderBy("s.rowNum", "ASC")
            ->addOrderBy("s.seatNumInRow", "ASC")
            ->getQuery()
            ->getResult();
    }


    public function updateSeatsInRange(
        ScreeningRoom $screeningRoom,
        int $rowNumStart,
        int $rowNumEnd,
        int $seatNumInRowStart,
        int $seatNumInRowEnd,
        ScreeningRoomSeatType $updatedType,
        PriceTier $updatedPriceTier
    ): int {
        $qb = $this->createQueryBuilder('srs')
            ->update()
            ->set('srs.type', ':updatedType')
            ->set('srs.priceTier', ':updatedPriceTier')
            ->andWhere('srs.screeningRoom = :screeningRoom')
            ->andWhere('srs.seat IN (
                        SELECT seat.id FROM App\Entity\Seat seat
                        WHERE seat.rowNum BETWEEN :rowNumStart AND :rowNumEnd
                        AND seat.seatNumInRow BETWEEN :seatNumInRowStart AND :seatNumInRowEnd
            )');

        $qb->setParameter('updatedType', $updatedType)
            ->setParameter('updatedPriceTier', $updatedPriceTier)
            ->setParameter('screeningRoom', $screeningRoom)
            ->setParameter('rowNumStart', $rowNumStart)
            ->setParameter('rowNumEnd', $rowNumEnd)
            ->setParameter('seatNumInRowStart', $seatNumInRowStart)
            ->setParameter('seatNumInRowEnd', $seatNumInRowEnd);

        return $qb->getQuery()->execute();
    }


    public function findByScreeningRoomQuery(ScreeningRoom $screeningRoom): Query
    {
        return $this->createQueryBuilder('srs')
            ->andWhere('srs.screeningRoom = :screeningRoom')
            ->innerJoin('srs.priceTier', 'pt')
            ->setParameter('screeningRoom', $screeningRoom)
            ->getQuery();
    }
}
