<?php

namespace App\Factory;

use App\Entity\ScreeningRoomSetup;
use Zenstruck\Foundry\Persistence\PersistentProxyObjectFactory;

/**
 * @extends PersistentProxyObjectFactory<ScreeningRoomSetup>
 */
final class ScreeningRoomSetupFactory extends PersistentProxyObjectFactory
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
        return ScreeningRoomSetup::class;
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
            'soundFormat' =>self::faker()->unique()->randomElement(["Dolby Atmos", "Dolby Digital 5.1"]),
            'visualFormat' => VisualFormatFactory::random(),
        ];
    }

    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#initialization
     */
    protected function initialize(): static
    {
        return $this
            // ->afterInstantiate(function(ScreeningRoomSetup $screeningRoomSetup): void {})
        ;
    }
}
