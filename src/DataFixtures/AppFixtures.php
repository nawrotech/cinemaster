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

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {

        SeatFactory::createGrid();
        $cinemas = CinemaFactory::createMany(3);

        foreach($cinemas as $cinema) {
            CinemaSeatFactory::createForCinema($cinema);
        }

        $screeningRooms = ScreeningRoomFactory::createScreeningRoomsForCinemas(10);

        foreach($screeningRooms as $screeningRoom) {
            ScreeningRoomSeatFactory::createForScreeningRoom($screeningRoom);
        }

        $movies = MovieFactory::createMany(20);
        $movieTypes = MovieTypeFactory::createFormatCombinations();
            
        $movieFormats = MovieMovieTypeFactory::createMany(count($movies), function() use($movies, $movieTypes) {
            return [
                "movie" => $movies[array_rand($movies)],
                "movieType" => $movieTypes[array_rand($movieTypes)]
            ];
        });

        ShowtimeFactory::createOne([
            "screeningRoom" => $screeningRooms[array_rand($screeningRooms)],
            "cinema" => $cinemas[array_rand($cinemas)],
            "movieFormat" => $movieFormats[array_rand($movieFormats)],
            "startTime" => new \DateTime("2024-10-10 T10:00:00P"),
            "endTime" => new \DateTime()
        ]);

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
