<?php

namespace App\Factory;

use App\Entity\Seat;
use Zenstruck\Foundry\Persistence\PersistentProxyObjectFactory;

/**
 * @extends PersistentProxyObjectFactory<Seat>
 */
final class SeatFactory extends PersistentProxyObjectFactory
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
        return Seat::class;
    }

    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#model-factories
     *
     * @todo add your default values here
     */
    protected function defaults(): array|callable
    {
        return [
            'colNum' => 1,
            'rowNum' => 1,
        ];
    }

    public static function createGrid(int $rows = 25, int $seatsPerRow = 25): array
    {
        $seats = [];
        
        for ($row = 1; $row <= $rows; $row++) {
            for ($col = 1; $col <= $seatsPerRow; $col++) {
                $seats[] = self::createOne([
                    'rowNum' => $row,
                    'colNum' => $col,
                ]);
            }
        }
        
        return $seats;
    }

    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#initialization
     */
    protected function initialize(): static
    {
        return $this
            // ->afterInstantiate(function(Seat $seat): void {})
        ;
    }
}
