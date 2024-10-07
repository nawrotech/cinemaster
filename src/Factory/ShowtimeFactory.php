<?php

namespace App\Factory;

use App\Entity\Showtime;
use Zenstruck\Foundry\Persistence\PersistentProxyObjectFactory;

/**
 * @extends PersistentProxyObjectFactory<Showtime>
 */
final class ShowtimeFactory extends PersistentProxyObjectFactory
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
        return Showtime::class;
    }

    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#model-factories
     *
     * @todo add your default values here
     */
    protected function defaults(): array|callable
    {
        return [
            'advertisementTimeInMinutes' => self::faker()->numberBetween(10, 20),
            'price' => self::faker()->numberBetween(15, 30),
            'startTime' => self::faker()->dateTime(),
            'endTime' => self::faker()->dateTime(),
            // 'movieFormat' => MovieMovieTypeFactory::new(),
            // 'published' => self::faker()->boolean(),
            // 'cinema' => CinemaFactory::new(),
            // 'screeningRoom' => ScreeningRoomFactory::new(),
        ];
    }

    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#initialization
     */
    protected function initialize(): static
    {
        return $this
            // ->afterInstantiate(function(Showtime $showtime): void {})
        ;
    }
}
