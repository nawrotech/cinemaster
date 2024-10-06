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
            'name' => self::faker()->word(),
            'rowsMax' => self::faker()->numberBetween(5, 12),
            'seatsPerRowMax' => self::faker()->numberBetween(5, 12),
            // 'createdAt' => \DateTimeImmutable::createFromMutable(self::faker()->dateTime("now")),
            // 'updatedAt' => \DateTimeImmutable::createFromMutable(self::faker()->dateTime("now")),
            // 'slug' => self::faker()->text(100),
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
