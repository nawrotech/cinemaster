<?php

namespace App\Tests;

use App\Factory\CinemaFactory;
use App\Factory\MovieMovieTypeFactory;
use App\Factory\ScreeningRoomFactory;
use App\Factory\ShowtimeFactory;
use App\Repository\ShowtimeRepository;
use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Zenstruck\Foundry\Test\Factories;

class ShowtimeRepositoryTest extends KernelTestCase
{
    use Factories;
    const BASE_DATE = "2024-10-10";

    private ?EntityManager $entityManager;

    protected function setUp(): void
    {
        $kernel = self::bootKernel();

        $this->entityManager = $kernel->getContainer()
            ->get('doctrine')
            ->getManager();
        
    }

    /**
     * @dataProvider overlapScenarioProvider
     */
    public function testOverlappingMovies(
        string $newShowStartsAt,
        string $newShowEndsAt,
        bool $shouldOverlap,
        string $scenario
    ): void
    {
        self::bootKernel();

        $showtimeStartsAt = sprintf("%s %s", self::BASE_DATE, "10:00:00");
        $showtimeEndsAt = sprintf("%s %s", self::BASE_DATE, "12:00:00");
       
         ShowtimeFactory::createOne([
            "screeningRoom" => ScreeningRoomFactory::createOne(),
            "cinema" => CinemaFactory::createOne(),
            "movieFormat" => MovieMovieTypeFactory::new(),
            "startTime" => new \DateTime($showtimeStartsAt),
            "endTime" => new \DateTime($showtimeEndsAt)
        ]);

        $newShowStartsAt = sprintf("%s %s", self::BASE_DATE, $newShowStartsAt);
        $newShowEndsAt = sprintf("%s %s", self::BASE_DATE, $newShowEndsAt);

        $result = $this->getShowtimeRepository()->findOverlapping(
            new \DateTime($newShowStartsAt),
            new \DateTime($newShowEndsAt)
        )->getQuery()->getResult();

        $expectedCount = $shouldOverlap ? 1 : 0;

        $this->assertCount(
            $expectedCount,
            $result,
            "Failed scenario: $scenario"
        );
        
    }



    public static function overlapScenarioProvider(): array
    {
        return [
            // No overlap scenarios
            'Before - No Overlap' => ['08:00:00', '09:59:00', false, 'Ends just before start'],
            'After - No Overlap' => ['12:00:00', '14:00:00', false, 'Starts exactly at end'],
            
            // Partial overlap scenarios
            'Overlap Start' => ["09:00:00", "11:00:00", true, 'Overlaps at start'],
            'Overlap End' => ["11:00:00", "13:00:00", true, 'Overlaps at end'],
            
            // Complete overlap scenarios
            'Exact Same Time' => ['10:00:00', '12:00:00', true, 'Exact same timeframe'],
            'Completely Contains' => ['09:00:00', '13:00:00', true, 'New showtime contains existing'],
            'Completely Contained' => ['10:30:00', '11:30:00', true, 'New showtime inside existing'],
            
            // Edge cases
            'Edge - Ends at Start' => ['08:00:00', '10:00:00', false, 'Ends exactly at start time'],
            'Edge - Starts at End' => ['12:00:00', '14:00:00', false, 'Starts exactly at end time'],
        ];
    }

    


    private function getShowtimeRepository(): ShowtimeRepository {
        return self::getContainer()->get(ShowtimeRepository::class);
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        $this->entityManager->close();
        $this->entityManager = null;
    }


}
