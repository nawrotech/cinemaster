<?php

namespace App\Repository;

use App\Entity\Cinema;
use App\Entity\ScreeningFormat;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
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
        string $screeningFormat): array
    {
        return $this->createQueryBuilder('sf')
                        ->innerJoin("sf.visualFormat", "vf")
                        ->andWhere("sf.languagePresentation LIKE :screeningFormat
                        OR
                        vf.name LIKE :screeningFormat")
                        ->andWhere("sf.cinema = :cinema")
                        ->setParameter("cinema", $cinema)
                        ->setParameter("screeningFormat", "%$screeningFormat%")
                        ->getQuery()
                        ->getResult()
        ;
    }

  
}
