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
        $qb = $this->filterByCinema($cinema);

        if ($isActive !== null) {
            $qb = $this->filterByActiveStatus($isActive, $qb);
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
        
        $qb = $this->filterByCinema($cinema);
        $qb = $this->filterByActiveStatus(true, $qb);
        $qb = $this->filterBySoundFormat($soundFormat, $qb);
        $qb = $this->filterByVisualFormatName($visualFormat->getName(), $qb);
        
        return $qb->getQuery()->getResult();
    }

 
    public function filterByCinema(Cinema $cinema, ?QueryBuilder $qb = null): QueryBuilder 
    {
        return ($qb ?? $this->createQueryBuilder("srs"))
                ->andWhere('srs.cinema = :cinema')
                ->setParameter('cinema', $cinema);
    }

  
    public function filterByActiveStatus(bool $isActive, ?QueryBuilder $qb = null): QueryBuilder 
    {
        return ($qb ?? $this->createQueryBuilder("srs"))
                ->andWhere('srs.isActive = :active')
                ->setParameter('active', $isActive);
    }

    public function filterBySoundFormat(string $soundFormat, ?QueryBuilder $qb = null): QueryBuilder 
    {
        return ($qb ?? $this->createQueryBuilder("srs"))
                ->andWhere('srs.soundFormat = :soundFormat')
                ->setParameter('soundFormat', $soundFormat);
    }


    public function filterByVisualFormatName(string $visualFormatName, ?QueryBuilder $qb = null): QueryBuilder 
    {
        return ($qb ?? $this->createQueryBuilder("srs"))
                ->innerJoin('srs.visualFormat', 'vf')
                ->andWhere('vf.name = :visualFormatName')
                ->setParameter('visualFormatName', $visualFormatName);
    }


    public function hasActiveSetupForCinema(Cinema $cinema): bool
    {
        $count = $this->filterByCinema($cinema)
            ->select('COUNT(srs.id)');

        $count = $this->filterByActiveStatus(true, $count);
                    
        $count = $count->getQuery()->getSingleScalarResult();

        return $count > 0;
    }

    public static function activeScreeningRoomSetupConstraint(bool $isActive = true): Criteria
    {
        $expressionBuilder = Criteria::expr();
        
        $criteria = Criteria::create();
        return $criteria->where($expressionBuilder->eq('isActive', $isActive));
    }
}
