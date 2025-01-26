<?php

namespace App\Integration\Service;

use App\Entity\Seat;
use App\Service\SeatService;
use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Zenstruck\Foundry\Test\Factories;

class SeatServiceTest extends KernelTestCase
{

    use Factories;
    private ?EntityManager $entityManager;

    protected function setUp(): void
    {
        $kernel = self::bootKernel();
        $this->entityManager = $kernel->getContainer()
            ->get('doctrine')
            ->getManager();

    }

    public function testSeatsAreFetchedAndCorrectlyStructured() 
    {
        $container = static::getContainer();
        $seatService = $container->get(SeatService::class);

        $groupedSeats = $seatService->groupSeatsByRow([1 => 2, 2 => 5, 3 => 2]);

        $this->assertIsArray($groupedSeats);
        $this->assertCount(3, $groupedSeats);

        foreach ($groupedSeats as $rowNum => $seats) {
            $this->assertIsArray($seats);
            $this->assertCount(5, $seats);
            foreach ($seats as $seat) {
                $this->assertInstanceOf(Seat::class, $seat);
                $this->assertEquals($rowNum, $seat->getRowNum());
            }
        }

        
    }



    protected function tearDown(): void
    {
        parent::tearDown();

        $this->entityManager->close();
        $this->entityManager = null;
    }
}
