<?php

namespace App\Repository;

use App\Entity\Cinema;
use App\Entity\ScreeningFormat;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Collections\Criteria;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<MovieType>
 */
class ScreeningFormatRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ScreeningFormat::class);
    }


    /**
     * @return ScreeningFormat[] Returns an array of ScreeningFormat objects
     */
    public function findByCinemaAndActiveStatus(Cinema $cinema, ?bool $isActive = null): array
    {
        $qb = $this->createQueryBuilder('sf')
            ->andWhere('sf.cinema = :cinema')
            ->setParameter('cinema', $cinema);

        if ($isActive !== null) {
            $qb->addCriteria($this->activeScreeningFormatCriteria($isActive));
        }

        return $qb->getQuery()->getResult();
    }

    public static function activeScreeningFormatCriteria(?bool $isActive): Criteria
    {
        $criteria = Criteria::create();

        if ($isActive !== null) {
            $criteria->andWhere(Criteria::expr()->eq('active', $isActive));
        }

        return $criteria;
    }

    public function findByIds(array $screeningFormatIds): array
    {
        return $this->createQueryBuilder('sf')
            ->andWhere("sf.if IN :screeningFormatIds")
            ->setParameter("screeningFormatIds", $screeningFormatIds)
            ->getQuery()
            ->getResult()
        ;
    }

    public function findScreeningFormatsBySearchedTermForCinema(
        Cinema $cinema,
        string $screeningFormatTerm,
        bool $isActive = true
    ): array {
        return $this->createQueryBuilder('sf')
            ->innerJoin("sf.visualFormat", "vf")
            ->andWhere("sf.languagePresentation LIKE :screeningFormatTerm
                        OR
                        vf.name LIKE :screeningFormatTerm")
            ->andWhere("sf.cinema = :cinema")
            ->setParameter("cinema", $cinema)
            ->setParameter("screeningFormatTerm", "%$screeningFormatTerm%")
            ->addCriteria($this->activeScreeningFormatCriteria($isActive))
            ->getQuery()
            ->getResult()
        ;
    }
}
