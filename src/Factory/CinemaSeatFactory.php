<?php

namespace App\Factory;

use App\Entity\Cinema;
use App\Entity\CinemaSeat;
use Doctrine\Common\Collections\ArrayCollection;
use Zenstruck\Foundry\Persistence\PersistentProxyObjectFactory;
use Zenstruck\Foundry\Persistence\Proxy;

/**
 * @extends PersistentProxyObjectFactory<CinemaSeat>
 */
final class CinemaSeatFactory extends PersistentProxyObjectFactory
{
    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#factories-as-services
     *
     * @todo inject services if required
     */
    public function __construct()
    {
    }

    public static function class(): string
    {
        return CinemaSeat::class;
    }

    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#model-factories
     *
     * @todo add your default values here
     */
    protected function defaults(): array|callable
    {
        return [
            // 'status' => self::faker()->text("active"),
        ];
    }

    public static function createForCinema(Cinema|Proxy $cinema): array
    {
        // Getting cinema constraints
        $maxRow = $cinema->getRowsMax();
        $maxSeatsPerRow = $cinema->getSeatsPerRowMax();

        // Fetching the seats that match the cinema's row/column constraints
        $seats = SeatFactory::repository()->createQueryBuilder('s')
            ->where('s.rowNum <= :maxRow')
            ->andWhere('s.colNum <= :maxSeatsPerRow')
            ->setParameter('maxRow', $maxRow)
            ->setParameter('maxSeatsPerRow', $maxSeatsPerRow)
            ->getQuery()
            ->getResult();

        $cinemaSeats = [];
        foreach ($seats as $seat) {
            // Creating CinemaSeat records based on the cinema and each seat
            $cinemaSeats[] = self::createOne([
                'cinema' => $cinema,
                'seat' => $seat,
            ]);
        }

        return $cinemaSeats;
    }

    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#initialization
     */
    protected function initialize(): static
    {
        return $this
            // ->afterInstantiate(function(CinemaSeat $cinemaSeat): void {})
        ;
    }
}
