<?php

namespace App\Tests;

use App\Entity\MovieMovieType;
use App\Entity\Showtime;
use App\Factory\CinemaFactory;
use App\Factory\MovieFactory;
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

    /**
     * @dataProvider overlapScenarioProvider
     */
    public function testOverlappingShows(
        string $newShowStartsAt,
        string $newShowEndsAt,
        bool $shouldOverlap,
        string $scenario
    ): void
    {
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

    public function testExcludedIdIsIgnored() {

        $showtimeStartsAt = sprintf("%s %s", self::BASE_DATE, "10:00:00");
        $showtimeEndsAt = sprintf("%s %s", self::BASE_DATE, "12:00:00");
       
        $showtime = ShowtimeFactory::createOne([
            "screeningRoom" => ScreeningRoomFactory::createOne(),
            "cinema" => CinemaFactory::createOne(),
            "movieFormat" => MovieMovieTypeFactory::new(),
            "startTime" => new \DateTime($showtimeStartsAt),
            "endTime" => new \DateTime($showtimeEndsAt)
        ]);

        assert($showtime instanceof Showtime);

        $updatedShowtimeStartsAt = sprintf("%s %s", self::BASE_DATE, "11:00:00");
        $updatedShowtimeEndsAt = sprintf("%s %s", self::BASE_DATE, "12:00:00");

        $resultForExisting = $this->getShowtimeRepository()->findOverlapping(
            new \DateTime($updatedShowtimeStartsAt),
            new \DateTime($updatedShowtimeEndsAt),
            $showtime->getId()
        )->getQuery()->getResult();

        $resultForNew = $this->getShowtimeRepository()->findOverlapping(
            new \DateTime($updatedShowtimeStartsAt),
            new \DateTime($updatedShowtimeEndsAt),
        )->getQuery()->getResult();

        $this->assertNotEquals($resultForExisting, $resultForNew);

    }

    public function testSameMovieIsPlayingAtTheSameTimeInDifferentRoom() {
        $showtimeStartsAt = sprintf("%s %s", self::BASE_DATE, "10:00:00");
        $showtimeEndsAt = sprintf("%s %s", self::BASE_DATE, "12:00:00");

        $movieFormat = MovieMovieTypeFactory::createOne(
            [
                "movie" => MovieFactory::new()
            ]
        );

        assert($movieFormat instanceof MovieMovieType);
        $actualMovieFormat = $this->entityManager
                                ->find(MovieMovieType::class, $movieFormat->getId());

     
        ShowtimeFactory::createOne([
            "screeningRoom" => ScreeningRoomFactory::createOne(),
            "cinema" => CinemaFactory::createOne(),
            "movieFormat" => $actualMovieFormat,
            "startTime" => new \DateTime($showtimeStartsAt),
            "endTime" => new \DateTime($showtimeEndsAt)
        ]);

        $resultOverlapping = $this->getShowtimeRepository()
            ->findOverlappingForMovie($actualMovieFormat, new \DateTime($showtimeStartsAt), new \DateTime($showtimeEndsAt));

        $resultNotOverlapping = $this->getShowtimeRepository()
            ->findOverlappingForMovie($actualMovieFormat, new \DateTime(self::BASE_DATE . " 12:00:00"), new \DateTime(self::BASE_DATE . " 13:00:00"));
        
        $this->assertCount(1, $resultOverlapping);
        $this->assertEmpty($resultNotOverlapping);


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
