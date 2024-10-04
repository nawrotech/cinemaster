<?php

namespace App\Repository;

use App\Entity\Cinema;
use App\Entity\CinemaHistory;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Cinema>
 */
class CinemaRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Cinema::class);
    }


    public function findOrderedCinemas(string $seatStatus = "active")
    {
        return $this->createQueryBuilder('c')
            ->addSelect("cs")
            ->innerJoin("c.cinemaSeats", "cs")
            ->orderBy("c.name", "DESC")
            ->andWhere("cs.status = :seatStatus")
            ->setParameter("seatStatus", $seatStatus)
            ->getQuery()
            ->getResult();
    }

    public function findMax(Cinema $cinema)
    {
        return $this->createQueryBuilder('c')
            ->leftJoin("c.cinemaSeats", "cs")
            ->leftJoin("cs.seat", "s")
            ->select("COUNT(DISTINCT s.rowNum) AS maxRowNum", "COUNT(DISTINCT s.colNum) AS maxColNum")
            ->andWhere("c = :cinema")

            ->setParameter("cinema", $cinema)
            ->getQuery()
            ->getSingleResult();
    }






    public function saveCinemaChanges(Cinema $cinema): void
    {
        $unitOfWork = $this->getEntityManager()->getUnitOfWork();
        $unitOfWork->computeChangeSets();
        $changeset = $unitOfWork->getEntityChangeSet($cinema);

        dd($changeset);

        if (!empty($changeset)) {
            $history = new CinemaHistory();
            $history->setCinema($cinema)
                ->setChanges($changeset)
                ->setChangedAt(new \DateTimeImmutable());

            $this->getEntityManager()->persist($history);
            $this->getEntityManager()->flush();
        }
    }




    //    /**
    //     * @return Cinema[] Returns an array of Cinema objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('c')
    //            ->andWhere('c.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('c.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Cinema
    //    {
    //        return $this->createQueryBuilder('c')
    //            ->andWhere('c.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
