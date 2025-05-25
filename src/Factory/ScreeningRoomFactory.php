<?php

namespace App\Factory;

use App\Entity\Cinema;
use App\Entity\ScreeningRoom;
use Zenstruck\Foundry\Persistence\PersistentProxyObjectFactory;

/**
 * @extends PersistentProxyObjectFactory<ScreeningRoom>
 */
final class ScreeningRoomFactory extends PersistentProxyObjectFactory
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
        return ScreeningRoom::class;
    }

    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#model-factories
     *
     * @todo add your default values here
     */
    protected function defaults(): array|callable
    {
        return [
            'createdAt' => \DateTimeImmutable::createFromMutable(self::faker()->dateTime()),
            'slug' => self::faker()->text(100),
            'status' => self::faker()->text(100),
            'name' => self::faker()->unique()->word(),
            "maintenanceTimeInMinutes" => self::faker()->numberBetween(5, 20),
            'screeningRoomSetup' => ScreeningRoomSetupFactory::random()
        ];
    }


    public static function createScreeningRoomsForCinemas(int $count): array
    {
        return self::createMany($count, function () {
            $cinema = CinemaFactory::random();
            assert($cinema instanceof Cinema);

            return [
                "cinema" => $cinema,
                "name" => self::faker()->unique()->word(),
                "maintenanceTimeInMinutes" => self::faker()->numberBetween(5, 20)

            ];
        });
    }


        

    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#initialization
     */
    protected function initialize(): static
    {
        return $this
            // ->afterInstantiate(function(ScreeningRoom $screeningRoom): void {})
        ;
    }
}
