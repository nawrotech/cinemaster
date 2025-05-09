<?php

namespace App\Repository;

use App\Entity\Cinema;
use App\Entity\ScreeningRoomSetup;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Collections\Criteria;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ScreeningSetupType>
 */
class ScreeningRoomSetupRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ScreeningRoomSetup::class);
    }

    /**
     * @return ScreeningRoomSetup[] Returns an array of ScreeningRoomSetup objects
     */
    public function findByCinemaAndActiveStatus(Cinema $cinema, ?bool $isActive = null): array
    {
        $qb = $this->createQueryBuilder('srs')
            ->andWhere('srs.cinema = :cinema')
            ->setParameter('cinema', $cinema);

        if ($isActive !== null) {
            $qb->andWhere('srs.isActive = :active')
                ->setParameter('active', $isActive);
        }

        return $qb
            ->getQuery()
            ->getResult();
    }

    public static function activeVisualFormatsConstraint(bool $isActive = true): Criteria
    {
        $expressionBuilder = Criteria::expr();

        $criteria = Criteria::create();
        return $criteria->where($expressionBuilder->eq('isActive', $isActive));
    }


    public function hasActiveSetupForCinema(Cinema $cinema): bool
    {
        $count = $this->createQueryBuilder('srs')
            ->select('COUNT(srs.id)')
            ->andWhere('srs.cinema = :cinema')
            ->andWhere('srs.isActive = :active')
            ->setParameter("cinema", $cinema)
            ->setParameter("active", true)
            ->getQuery()
            ->getSingleScalarResult();

        return $count > 0;
    }
}
