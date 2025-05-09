<?php

namespace App\Repository;

use App\Entity\Cinema;
use App\Entity\VisualFormat;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Collections\Criteria;
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
        $qb = $this->createQueryBuilder('v')
            ->andWhere('v.cinema = :cinema')
            ->setParameter('cinema', $cinema);

        if ($isActive !== null) {
            $qb->addCriteria(self::activeVisualFormatsConstraint($isActive));
        }

        return $qb->getQuery()->getResult();
    }


    public static function activeVisualFormatsConstraint(bool $isActive = true): Criteria 
    {
        $expressionBuilder = Criteria::expr();

        $criteria = Criteria::create();
        return $criteria->where($expressionBuilder->eq('active', $isActive));
    }

}
