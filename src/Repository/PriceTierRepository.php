<?php

namespace App\Repository;

use App\Entity\Cinema;
use App\Entity\PriceTier;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<PriceTier>
 */
class PriceTierRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PriceTier::class);
    }


    public function findActiveByCinema(array $fieldValues): ?array
    {

        $name = $fieldValues['name'] ?? null;
        $price = $fieldValues['price'] ?? null;
        $cinema = $fieldValues['cinema'] ?? null;
        
        if (!$name || !$price || !$cinema) {
            return null;
        }

        $qb = $this->filterByCinema($cinema);

        $qb = $this->filterByName($name, $qb);
        
        $qb = $this->filterByPrice($price, $qb);

        $qb = $this->filterByActiveStatus(true, $qb);

        return $qb->getQuery()->getResult();
    }


    /**
    * @return PriceTier[] Returns an array of VisualFormat objects
    */
    public function findByCinemaAndActiveStatus(Cinema $cinema, ?bool $isActive = true): array
    {
        $qb = $this->filterByCinema($cinema);

        if ($isActive !== null) {
            $qb = $this->filterByActiveStatus($isActive, $qb);
        }

        return $qb->getQuery()->getResult();
    }


    public function filterByCinema(Cinema $cinema, ?QueryBuilder $qb = null): QueryBuilder {
        return ($qb ?? $this->createQueryBuilder("pt"))
                ->andWhere('pt.cinema = :cinema')
                ->setParameter('cinema', $cinema);
    }

    public function filterByActiveStatus(bool $isActive, ?QueryBuilder $qb = null): QueryBuilder {
        return ($qb ?? $this->createQueryBuilder("pt"))
                ->andWhere('pt.isActive = :isActive')
                ->setParameter('isActive', $isActive);
    }

    public function filterByName(string $name, ?QueryBuilder $qb = null): QueryBuilder {
        return ($qb ?? $this->createQueryBuilder("pt"))
                ->andWhere('pt.name = :name')
                ->setParameter('name', $name);
    }


    public function filterByPrice(float $price, ?QueryBuilder $qb = null): QueryBuilder {
        return ($qb ?? $this->createQueryBuilder("pt"))
                ->andWhere('pt.price = :price')
                ->setParameter('price', $price);
    }


    public static function activePriceTierConstraint(bool $isActive = true): Criteria 
    {
        $expressionBuilder = Criteria::expr();

        $criteria = Criteria::create();
        return $criteria->where($expressionBuilder->eq('isActive', $isActive));
    }


}
