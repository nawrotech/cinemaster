<?php

namespace App\DataFixtures;

use App\Factory\CinemaFactory;
use App\Factory\CinemaSeatFactory;
use App\Factory\ScreeningRoomFactory;
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

        // $screeningRooms = ScreeningRoomFactory::createMany(10, function() {
        //     return [
        //         "cinema" => CinemaFactory::random(),
        //         'rowsMax' => faker()->numberBetween(5, cinemarowmax),
        //         'seatsPerRowMax' => faker()->numberBetween(5, cinemaxseatsperrowmax),

        //     ];
        // });

        $screeningRooms = ScreeningRoomFactory::createScreeningRoomsForCinemas(10);

        foreach($screeningRooms as $screeningRoom) {
            
        }









        



        
     
        $manager->flush();
    }
}
