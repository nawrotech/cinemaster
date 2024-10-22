<?php

namespace App\Factory;

use App\Entity\Cinema;
use Zenstruck\Foundry\Persistence\PersistentProxyObjectFactory;

/**
 * @extends PersistentProxyObjectFactory<Cinema>
 */
final class CinemaFactory extends PersistentProxyObjectFactory
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
        return Cinema::class;
    }

    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#model-factories
     *
     * @todo add your default values here
     */
    protected function defaults(): array|callable
    {
        return [
            'buildingNumber' => self::faker()->buildingNumber(),
            'city' => self::faker()->city(),
            'country' => self::faker()->country(),
            'createdAt' => \DateTimeImmutable::createFromMutable(self::faker()->dateTime()),
            'district' => self::faker()->state(),
            'maxRows' => self::faker()->numberBetween(5, 15),
            'maxSeatsPerRow' => self::faker()->numberBetween(5, 15),
            'name' => self::faker()->word(),
            'postalCode' => self::faker()->numberBetween(9999, 99999),
            'streetName' => self::faker()->streetName(),
            'updatedAt' => \DateTimeImmutable::createFromMutable(self::faker()->dateTime()),
        ];
    }

    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#initialization
     */
    protected function initialize(): static
    {
        return $this
            // ->afterInstantiate(function(Cinema $cinema): void {})
        ;
    }
}
