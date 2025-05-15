<?php

namespace App\Repository;

use App\Entity\Cinema;
use App\Entity\ScreeningFormat;
use App\Enum\LanguagePresentation;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\QueryBuilder;
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
        $qb = $this->filterByCinema($cinema);

        if ($isActive !== null) {
            $qb = $this->filterByActiveStatus($isActive, $qb);
        }

        return $qb->getQuery()->getResult();
    }

    public function findActiveByCinema(array $fieldValues)
    {
        $visualFormat = $fieldValues['visualFormat'] ?? null;
        $languagePresentation = $fieldValues['languagePresentation'] ?? null;
        $cinema = $fieldValues['cinema'] ?? null;

        if (!$visualFormat || !$languagePresentation || !$cinema) {
            return null;
        }

        if (is_string($languagePresentation)) {
            $languagePresentation = LanguagePresentation::tryFrom($languagePresentation);

            if (!$languagePresentation) {
                return null;
            }
        }

        $qb = $this->filterByCinema($cinema);

        $qb = $this->filterByActiveStatus(true, $qb);

        $qb = $this->filterByLanguagePresentation($languagePresentation, $qb);

        $qb = $this->filterByVisualFormatName($visualFormat->getName(), $qb);

        return $qb->getQuery()->getResult();
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
        $qb =  $this->createQueryBuilder('sf')
            ->innerJoin("sf.visualFormat", "vf")
            ->andWhere("LOWER(sf.languagePresentation) LIKE LOWER(:screeningFormatTerm)
                        OR
                        LOWER(vf.name) LIKE LOWER(:screeningFormatTerm)")
            ->setParameter("screeningFormatTerm", "%$screeningFormatTerm%");

        $qb = $this->filterByCinema($cinema, $qb);

        $qb = $this->filterByActiveStatus($isActive, $qb);

        return $qb->getQuery()->getResult();
    }


    public function filterByCinema(Cinema $cinema, ?QueryBuilder $qb = null): QueryBuilder {
        return ($qb ?? $this->createQueryBuilder("sf"))
                ->andWhere('sf.cinema = :cinema')
                ->setParameter('cinema', $cinema);
    }

    public function filterByActiveStatus(bool $isActive, ?QueryBuilder $qb = null): QueryBuilder {
        return ($qb ?? $this->createQueryBuilder("sf"))
                ->andWhere('sf.active = :active')
                ->setParameter('active', $isActive);
    }

    public function filterByVisualFormatName(string $visualFormatName, ?QueryBuilder $qb = null): QueryBuilder {
        return ($qb ?? $this->createQueryBuilder("sf"))
                ->innerJoin('sf.visualFormat', 'vf')
                ->andWhere('vf.name = :visualFormatName')
                ->setParameter('visualFormatName', $visualFormatName);
    }

    public function filterByLanguagePresentation(LanguagePresentation $languagePresentation, ?QueryBuilder $qb = null): QueryBuilder {
        return ($qb ?? $this->createQueryBuilder("sf"))
                ->andWhere('sf.languagePresentation = :languagePresentation')
                ->setParameter('languagePresentation', $languagePresentation);
    }


    public static function activeScreeningFormatCriteria(?bool $isActive = true): Criteria
    {
        $criteria = Criteria::create();

        if ($isActive !== null) {
            $criteria->andWhere(Criteria::expr()->eq('active', $isActive));
        }

        return $criteria;
    }
}
