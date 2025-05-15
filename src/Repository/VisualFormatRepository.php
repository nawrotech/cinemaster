<?php

namespace App\Repository;

use App\Entity\Cinema;
use App\Entity\VisualFormat;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<VisualFormat>
 */
class VisualFormatRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, VisualFormat::class);
    }

    /**
    * @return VisualFormat[] Returns an array of VisualFormat objects
    */
    public function findByCinemaAndActiveStatus(Cinema $cinema, ?bool $isActive = null): array
    {
        $qb = $this->filterByCinema($cinema);

        if ($isActive !== null) {
            $qb = $this->filterByActiveStatus($isActive, $qb);
        }

        return $qb->getQuery()->getResult();
    }

    public function findActiveByCinema(array $fieldValues): ?array
    {
        $name = $fieldValues['name'] ?? null;
        $cinema = $fieldValues['cinema'] ?? null;
        
        if (!$name || !$cinema) {
            return null;
        }

        $qb = $this->filterByCinema($cinema);

        $qb = $this->filterByActiveStatus(true, $qb);

        $qb = $this->filterByName($name, $qb);

        return $qb->getQuery()->getResult();
    }


    public function filterByCinema(Cinema $cinema, ?QueryBuilder $qb = null): QueryBuilder {
        return ($qb ?? $this->createQueryBuilder("v"))
                ->andWhere('v.cinema = :cinema')
                ->setParameter('cinema', $cinema);
    }

    public function filterByActiveStatus(bool $isActive, ?QueryBuilder $qb = null): QueryBuilder {
        return ($qb ?? $this->createQueryBuilder("v"))
                ->andWhere('v.active = :active')
                ->setParameter('active', $isActive);
    }

    public function filterByName(string $name, ?QueryBuilder $qb = null): QueryBuilder {
        return ($qb ?? $this->createQueryBuilder("v"))
                ->andWhere('v.name = :name')
                ->setParameter('name', $name);
    }




    public static function activeVisualFormatsConstraint(bool $isActive = true): Criteria 
    {
        $expressionBuilder = Criteria::expr();

        $criteria = Criteria::create();
        return $criteria->where($expressionBuilder->eq('active', $isActive));
    }

}
