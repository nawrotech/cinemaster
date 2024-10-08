<?php

namespace App\DataFixtures;

use App\Entity\MovieMovieType;
use App\Entity\MovieType;
use App\Factory\CinemaFactory;
use App\Factory\CinemaSeatFactory;
use App\Factory\MovieFactory;
use App\Factory\MovieMovieTypeFactory;
use App\Factory\MovieTypeFactory;
use App\Factory\ScreeningRoomFactory;
use App\Factory\ScreeningRoomSeatFactory;
use App\Factory\SeatFactory;
use App\Factory\ShowtimeFactory;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

use function Zenstruck\Foundry\faker;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        SeatFactory::createGrid();
        $cinemas = CinemaFactory::createMany(2);

        foreach($cinemas as $cinema) {
            CinemaSeatFactory::createForCinema($cinema);
        }

        $screeningRooms = ScreeningRoomFactory::createScreeningRoomsForCinemas(10);

        foreach($screeningRooms as $screeningRoom) {
            ScreeningRoomSeatFactory::createForScreeningRoom($screeningRoom);
        }

        $movies = MovieFactory::createMany(2);
        $movieTypes = MovieTypeFactory::createMany(4);
            
        MovieMovieTypeFactory::createMany(count($movies), function() use($movies, $movieTypes) {
            return [
                "movie" => $movies[array_rand($movies)],
                "movieType" => $movieTypes[array_rand($movieTypes)]
            ];
        });

        ShowtimeFactory::createMany(10, function() use($screeningRooms) {
            $startsAt = \DateTimeImmutable::createFromMutable(faker()->dateTimeBetween("now", "+1 week"));
            $endsAt = $startsAt->modify('+' . rand(1, 3) . ' hours');
            
            $cinema = CinemaFactory::random();
            $screeningRooms = ScreeningRoomFactory::findBy(["cinema" => $cinema]);

            return [
                "cinema" => $cinema,
                "screeningRoom" =>  $screeningRooms[array_rand($screeningRooms)],
                "movieFormat" => MovieMovieTypeFactory::random(),
                "startTime" => $startsAt,
                "endTime" => $endsAt
            ];
        });

        // ShowtimeFactory::createOne([
        //     "screeningRoom" => ScreeningRoomFactory::createOne(),
        //     "cinema" => CinemaFactory::createOne(),
        //     "movieFormat" => MovieMovieTypeFactory::new(),
        //     "startTime" => new \DateTime("2024-10-10 T10:00:00P"),
        //     "endTime" => new \DateTime("2024-10-10 T12:00:00P")
        // ]);



        

        $manager->flush();
    }
}
