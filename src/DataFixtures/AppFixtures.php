<?php

namespace App\DataFixtures;
use App\Factory\CinemaFactory;
use App\Factory\ScreeningFormatFactory;
use App\Factory\ScreeningRoomFactory;
use App\Factory\ScreeningRoomSeatFactory;
use App\Factory\ScreeningRoomSetupFactory;
use App\Factory\SeatFactory;
use App\Factory\UserFactory;
use App\Factory\VisualFormatFactory;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class AppFixtures extends Fixture
{

    public function load(ObjectManager $manager): void
    {
        SeatFactory::createGrid();
        $user = UserFactory::createOne();

        CinemaFactory::createOne([
            "owner" => $user
        ]);

        VisualFormatFactory::createMany(2, function () {
            return [
                "cinema" => CinemaFactory::random()
            ];
        });

        ScreeningFormatFactory::createMany(2, function () {
            return [
                "cinema" => CinemaFactory::random()
            ];
        });

        ScreeningRoomSetupFactory::createMany(2, function () {
            return [
                "cinema" => CinemaFactory::random()
            ];
        });


        // $screeningRooms = ScreeningRoomFactory::createScreeningRoomsForCinemas(10);

        // foreach($screeningRooms as $screeningRoom) {
        //     ScreeningRoomSeatFactory::createForScreeningRoom($screeningRoom);
        // }

        $manager->flush();
    }
}
