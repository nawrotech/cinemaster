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
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

use function Zenstruck\Foundry\faker;

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

        
        $movieFormats = MovieTypeFactory::createFormatCombinations();
            
        MovieMovieTypeFactory::createMany(count($movies), function() use($movies, $movieFormats) {
            return [
                "movie" => $movies[array_rand($movies)],
                "movieType" => $movieFormats[array_rand($movieFormats)]
            ];
        });
        
        $manager->flush();
    }
}
