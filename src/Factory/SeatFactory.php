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
    public function __construct() {}

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
            'seatNumInRow' => 1,
            'rowNum' => 1,
        ];
    }

    public static function createGrid(int $rows = 25, int $seatsPerRow = 25): void
    {
        self::createSequence(
            function () use ($rows, $seatsPerRow) {
                foreach (range(1, $rows) as $row) {
                    foreach (range(1, $seatsPerRow) as $col) {
                        yield [
                            "rowNum" => $row,
                            "seatNumInRow" => $col
                        ];
                    }
                }
            }
        );
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
