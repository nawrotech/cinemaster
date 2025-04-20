<?php

namespace App\Factory;

use App\Entity\Movie;
use App\Service\UploaderHelper;
use Zenstruck\Foundry\Persistence\PersistentProxyObjectFactory;

/**
 * @extends PersistentProxyObjectFactory<Movie>
 */
final class MovieFactory extends PersistentProxyObjectFactory
{
    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#factories-as-services
     *
     * @todo inject services if required
     */
    public function __construct(private UploaderHelper $uploaderHelper) {}

    public static function class(): string
    {
        return Movie::class;
    }

    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#model-factories
     *
     * @todo add your default values here
     */
    protected function defaults(): array|callable
    {
        return [
            'title' => self::faker()->unique->word(),
            'overview' => self::faker()->text(),
            'durationInMinutes' => self::faker()->numberBetween(90, 180),
            // 'releaseDate' => self::faker()->dateTimeBetween('-1 year', 'now'),
            // "posterPath" => self::faker()
        ];
    }

    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#initialization
     */
    protected function initialize(): static
    {
        return $this
            // ->afterInstantiate(function(Movie $movie): void {})
        ;
    }
}
