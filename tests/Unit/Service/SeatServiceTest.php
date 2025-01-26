<?php

namespace App\Tests;

use App\Exception\InvalidRowsAndSeatsStructureException;
use App\Repository\SeatRepository;
use App\Service\SeatService;
use Doctrine\ORM\EntityManagerInterface;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class SeatServiceTest extends TestCase
{
    private SeatService $seatService;

    protected function setUp(): void
    {
        $seatRepository = $this->createMock(SeatRepository::class);
        $entityManager = $this->createMock(EntityManagerInterface::class);
        assert($seatRepository instanceof SeatRepository);
        assert($entityManager instanceof EntityManagerInterface);
        
        $this->seatService = new SeatService($seatRepository, $entityManager);;
    }

    public function testEmptyArrayThrowsInvalidArgumentException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->seatService->groupSeatsByRow([]);
    }

    /**
     * @dataProvider invalidArrayStructureProvider
     */
    public function testInvalidArrayStructureThrowsInvalidRowsAndSeatsStructureException(array $rowsAndSeats, string $expectedException): void 
    {
                
         $this->expectException($expectedException);
         $this->seatService->groupSeatsByRow($rowsAndSeats);
    }


    /**
     * Data provider supplying invalid array structures.
     *
     * @return array
     */
    public function invalidArrayStructureProvider(): array
    {
        return [
            'zero-based array' => [
                'rowsAndSeats' => [0 => 20, 1 => 30, 2 => 40],
                'expectedException' => InvalidRowsAndSeatsStructureException::class
            ],
            'non-sequential keys' => [
                'rowsAndSeats' => [1 => 20, 3 => 30, 4 => 40],
                'expectedException' => InvalidRowsAndSeatsStructureException::class
            ],
            'non-integer keys' => [
                'rowsAndSeats' => ['first' => 20, 'second' => 30, 'third' => 40],
                'expectedException' => InvalidRowsAndSeatsStructureException::class
            ],
            'negative keys' => [
                'rowsAndSeats' => [-1 => 20, -2 => 30, -3 => 40],
                'expectedException' => InvalidRowsAndSeatsStructureException::class
            ],
         
        ];
    }



    
}
