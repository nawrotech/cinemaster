<?php

namespace App\Tests;

use App\Entity\Showtime;
use App\Factory\CinemaFactory;
use App\Factory\MovieFactory;
use App\Factory\MovieScreeningFormatFactory;
use App\Factory\ScreeningRoomFactory;
use App\Factory\ShowtimeFactory;
use App\Repository\ShowtimeRepository;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Zenstruck\Foundry\Test\Factories;

class ShowtimeRepositoryTest extends KernelTestCase
{
    use Factories;
    const BASE_DATE = "2024-10-10";

    private ?EntityManagerInterface $entityManager;

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
       
        $cinema = CinemaFactory::createOne([
            'name' => 'test-cinema'
        ]);
        $realCinema = $cinema->_real();

        $screeningRoom = ScreeningRoomFactory::createOne([
            'name' => 'test-room',
            'cinema' => $cinema
        ]);

        $movie = MovieFactory::createOne([
            'cinema' => $cinema
        ]);

        $movieScreeningFormat = MovieScreeningFormatFactory::createOne([
            'cinema' => $cinema,
            'movie' => $movie,
        ]);

         ShowtimeFactory::createOne([
            "screeningRoom" => $screeningRoom,
            "cinema" => $realCinema,
            "movieScreeningFormat" => $movieScreeningFormat,
            "startsAt" => new \DateTime($showtimeStartsAt),
            "endsAt" => new \DateTime($showtimeEndsAt)
        ]);

        $newShowStartsAt = sprintf("%s %s", self::BASE_DATE, $newShowStartsAt);
        $newShowEndsAt = sprintf("%s %s", self::BASE_DATE, $newShowEndsAt);

        $result = $this->getShowtimeRepository()->findOverlapping(
            $realCinema,
            self::BASE_DATE,
            new \DateTimeImmutable($newShowStartsAt),
            new \DateTimeImmutable($newShowEndsAt),
        )->getQuery()->getResult();

        $expectedCount = $shouldOverlap ? 1 : 0;

        $this->assertCount(
            $expectedCount,
            $result,
            $scenario
        );
        
    }

    public function testExcludedIdIsIgnored() {
        $showtimeStartsAt = sprintf("%s %s", self::BASE_DATE, "10:00:00");
        $showtimeEndsAt = sprintf("%s %s", self::BASE_DATE, "12:00:00");
       
        $cinema = CinemaFactory::createOne([
            'name' => 'test-cinema'
        ])->_real();
        
        $movie = MovieFactory::createOne([
            'cinema' => $cinema
        ]);
        
        $movieScreeningFormat = MovieScreeningFormatFactory::createOne([
            'cinema' => $cinema,
            'movie' => $movie,
        ]);
        
        $screeningRoom = ScreeningRoomFactory::createOne([
            'name' => 'test-room',
            'cinema' => $cinema
        ]);
    
        $showtime = ShowtimeFactory::createOne([
            "screeningRoom" => $screeningRoom,
            "cinema" => $cinema,
            "movieScreeningFormat" => $movieScreeningFormat, 
            "startsAt" => new \DateTime($showtimeStartsAt), 
            "endsAt" => new \DateTime($showtimeEndsAt)     
        ])->_real();
    
        $updatedShowtimeStartsAt = sprintf("%s %s", self::BASE_DATE, "11:00:00");
        $updatedShowtimeEndsAt = sprintf("%s %s", self::BASE_DATE, "12:00:00");
    
        assert($showtime instanceof Showtime);
    
        $resultForExisting = $this->getShowtimeRepository()->findOverlapping(
            $cinema,
            self::BASE_DATE, 
            new \DateTimeImmutable($updatedShowtimeStartsAt),
            new \DateTimeImmutable($updatedShowtimeEndsAt),
            $showtime
        )->getQuery()->getResult();
    
        $resultForNew = $this->getShowtimeRepository()->findOverlapping(
            $cinema,
            self::BASE_DATE, 
            new \DateTimeImmutable($updatedShowtimeStartsAt),
            new \DateTimeImmutable($updatedShowtimeEndsAt),
        )->getQuery()->getResult();
    
        $this->assertNotEquals(count($resultForExisting), count($resultForNew));
    }


    private function getShowtimeRepository(): ShowtimeRepository {
        return self::getContainer()->get(ShowtimeRepository::class);
    }

 


}
