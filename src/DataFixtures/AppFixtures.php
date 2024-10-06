<?php

namespace App\DataFixtures;

use App\Factory\CinemaFactory;
use App\Factory\CinemaSeatFactory;
use App\Factory\SeatFactory;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        // $product = new Product();
        // $manager->persist($product);
        // MovieTypeFactory::createMany(3);

        SeatFactory::createGrid();
        $cinemas = CinemaFactory::createMany(3);

        foreach($cinemas as $cinema) {
            CinemaSeatFactory::createForCinema($cinema);
        }
        
     
        $manager->flush();
    }
}
