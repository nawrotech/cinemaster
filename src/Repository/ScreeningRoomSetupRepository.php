<?php

namespace App\Repository;

use App\Entity\Cinema;
use App\Entity\ScreeningRoomSetup;
use App\Entity\VisualFormat;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ScreeningRoomSetup>
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
        $qb = $this->findByCinema($cinema);

        if ($isActive !== null) {
            $qb = $this->findByActiveStatus($isActive, $qb);
        }

        return $qb->getQuery()->getResult();
    }

    /**
     * Used by UniqueEntity constraint
     * 
     * @param array $fieldValues The values to check for uniqueness
     * @return ScreeningRoomSetup[]
     */
    public function findActiveByCinema(array $fieldValues): ?array
    {
        $soundFormat = $fieldValues['soundFormat'] ?? null;
        $visualFormat = $fieldValues['visualFormat'] ?? null;
        $cinema = $fieldValues['cinema'] ?? null;
        
        if (!$soundFormat || !$visualFormat || !$cinema) {
            return null;
        }
        
        $qb = $this->findByCinema($cinema);
        $qb = $this->findByActiveStatus(true, $qb);
        $qb = $this->findBySoundFormat($soundFormat, $qb);
        $qb = $this->findByVisualFormat($visualFormat, $qb);
        
        return $qb->getQuery()->getResult();
    }

 
    public function findByCinema(Cinema $cinema, ?QueryBuilder $qb = null): QueryBuilder 
    {
        return ($qb ?? $this->createQueryBuilder("srs"))
                ->andWhere('srs.cinema = :cinema')
                ->setParameter('cinema', $cinema);
    }

  
    public function findByActiveStatus(bool $isActive, ?QueryBuilder $qb = null): QueryBuilder 
    {
        return ($qb ?? $this->createQueryBuilder("srs"))
                ->andWhere('srs.isActive = :active')
                ->setParameter('active', $isActive);
    }

    public function findBySoundFormat(string $soundFormat, ?QueryBuilder $qb = null): QueryBuilder 
    {
        return ($qb ?? $this->createQueryBuilder("srs"))
                ->andWhere('srs.soundFormat = :soundFormat')
                ->setParameter('soundFormat', $soundFormat);
    }


    public function findByVisualFormat(VisualFormat $visualFormat, ?QueryBuilder $qb = null): QueryBuilder 
    {
        return ($qb ?? $this->createQueryBuilder("srs"))
                ->andWhere('srs.visualFormat = :visualFormat')
                ->setParameter('visualFormat', $visualFormat);
    }

    public function hasActiveSetupForCinema(Cinema $cinema): bool
    {
        $count = $this->findByCinema($cinema)
            ->select('COUNT(srs.id)')
            ->andWhere('srs.isActive = :active')
            ->setParameter("active", true)
            ->getQuery()
            ->getSingleScalarResult();

        return $count > 0;
    }

    public static function activeScreeningRoomSetupConstraint(bool $isActive = true): Criteria
    {
        $expressionBuilder = Criteria::expr();
        
        $criteria = Criteria::create();
        return $criteria->where($expressionBuilder->eq('isActive', $isActive));
    }
}
