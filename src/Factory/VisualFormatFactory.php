<?php

namespace App\Factory;

use App\Entity\VisualFormat;
use Zenstruck\Foundry\Persistence\PersistentProxyObjectFactory;

/**
 * @extends PersistentProxyObjectFactory<VisualFormat>
 */
final class VisualFormatFactory extends PersistentProxyObjectFactory
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
        return VisualFormat::class;
    }

    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#model-factories
     *
     * @todo add your default values here
     */
    protected function defaults(): array|callable
    {
        return [
            'cinema' => CinemaFactory::random(),
            'name' => self::faker()->randomElement(["3D", "4K Ultra HD", "IMAX", "2D Digital Projection", "4K HDR", "Dolby Vision"]),
        ];
    }

    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#initialization
     */
    protected function initialize(): static
    {
        return $this
            // ->afterInstantiate(function(VisualFormat $visualFormat): void {})
        ;
    }
}
