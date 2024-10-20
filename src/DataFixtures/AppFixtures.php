<?php

namespace App\DataFixtures;

use App\Entity\MovieFormat;
use App\Factory\CinemaFactory;
use App\Factory\CinemaSeatFactory;
use App\Factory\FormatFactory;
use App\Factory\MovieFactory;
use App\Factory\MovieFormatFactory;
use App\Factory\MovieMovieTypeFactory;
use App\Factory\MovieTypeFactory;
use App\Factory\ScreeningRoomFactory;
use App\Factory\ScreeningRoomSeatFactory;
use App\Factory\SeatFactory;
use App\Factory\UserFactory;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class AppFixtures extends Fixture
{
    
    public function load(ObjectManager $manager): void
    {
        SeatFactory::createGrid();
        UserFactory::createOne();

        // CinemaFactory::createMany(2);

        // $screeningRooms = ScreeningRoomFactory::createScreeningRoomsForCinemas(10);

        // foreach($screeningRooms as $screeningRoom) {
        //     ScreeningRoomSeatFactory::createForScreeningRoom($screeningRoom);
        // }

        // $movies = MovieFactory::createMany(2);
        // FormatFactory::createMany(4);
            
        // MovieFormatFactory::createMany(count($movies), function()  {
        //     return [
        //         "movie" => MovieFactory::random(),
        //         "format" => FormatFactory::random()
        //     ];
        // });

        // ShowtimeFactory::createMany(10, function() use($screeningRooms) {
        //     $startsAt= \DateTimeImmutable::createFromMutable(faker()->dateTimeBetween("now", "+1 week"));
        //     $endsAt =  $startsAt->modify('+' . rand(1, 3) . ' hours');

        //     $cinema = CinemaFactory::random();
        //     $screeningRooms = ScreeningRoomFactory::findBy(["cinema" => $cinema]);

        //     return [
        //         "cinema" => $cinema,
        //         "screeningRoom" =>  $screeningRooms[array_rand($screeningRooms)],
        //         "movieFormat" => MovieMovieTypeFactory::random(),
        //         "startTime" => $startsAt,
        //         "endTime" => $endsAt
        //     ];
        // });

 



        

        $manager->flush();
    }
}
