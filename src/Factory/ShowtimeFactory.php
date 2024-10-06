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
            'advertisementTimeInMinutes' => self::faker()->randomNumber(),
            'cinema' => CinemaFactory::new(),
            'endTime' => self::faker()->dateTime(),
            'movieFormat' => MovieMovieTypeFactory::new(),
            'price' => self::faker()->randomNumber(),
            'published' => self::faker()->boolean(),
            'screeningRoom' => ScreeningRoomFactory::new(),
            'startTime' => self::faker()->dateTime(),
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
