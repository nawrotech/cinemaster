<?php

namespace App\Tests;

use App\Factory\ShowtimeFactory;
use App\Repository\ShowtimeRepository;
use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Zenstruck\Foundry\Test\Factories;

class ShowtimeRepositoryTest extends KernelTestCase
{
    // use Factories, ;

    private ?EntityManager $entityManager;

    protected function setUp(): void
    {
        $kernel = self::bootKernel();

        $this->entityManager = $kernel->getContainer()
            ->get('doctrine')
            ->getManager();
        
        
    }

    public function testOverlappingMovies(): void
    {


        // $showtime = $this->mock
        ShowtimeFactory::createOne();
        $this->assertCount(1, $this->getShowTimeRepository()->checkTests());



        // $this->assertSame('test', $kernel->getEnvironment());
        // $routerService = static::getContainer()->get('router');
        // $myCustomService = static::getContainer()->get(CustomService::class);
    }

    public function getShowTimeRepository(): ShowtimeRepository {
        return self::getContainer()->get(ShowtimeRepository::class);
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        // doing this is recommended to avoid memory leaks
        $this->entityManager->close();
        $this->entityManager = null;
    }

    // public static function overlappingTimesProvider(): array
    // {
    //     return [
    //         'Complete overlap' => [
    //             new DateTime('2024-03-20 13:00:00'),
    //             new DateTime('2024-03-20 17:00:00'),
    //             true,
    //             'Complete overlap'
    //         ],
    //         'New showtime starts during existing' => [
    //             new DateTime('2024-03-20 15:00:00'),
    //             new DateTime('2024-03-20 17:00:00'),
    //             true,
    //             'Start during'
    //         ],
    //         'New showtime ends during existing' => [
    //             new DateTime('2024-03-20 13:00:00'),
    //             new DateTime('2024-03-20 15:00:00'),
    //             true,
    //             'End during'
    //         ],
    //         'New showtime contained within existing' => [
    //             new DateTime('2024-03-20 14:30:00'),
    //             new DateTime('2024-03-20 15:30:00'),
    //             true,
    //             'Contained within'
    //         ],
    //         'No overlap - before' => [
    //             new DateTime('2024-03-20 11:00:00'),
    //             new DateTime('2024-03-20 13:00:00'),
    //             false,
    //             'Before'
    //         ],
    //         'No overlap - after' => [
    //             new DateTime('2024-03-20 17:00:00'),
    //             new DateTime('2024-03-20 19:00:00'),
    //             false,
    //             'After'
    //         ],
    //         'Edge case - starts exactly when other ends' => [
    //             new DateTime('2024-03-20 16:00:00'),
    //             new DateTime('2024-03-20 18:00:00'),
    //             false,
    //             'Edge case - start at end'
    //         ],
    //         'Edge case - ends exactly when other starts' => [
    //             new DateTime('2024-03-20 12:00:00'),
    //             new DateTime('2024-03-20 14:00:00'),
    //             false,
    //             'Edge case - end at start'
    //         ]
    //     ];
    // }
}
