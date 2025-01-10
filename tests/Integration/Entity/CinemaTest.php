<?php

namespace App\Tests;

use App\Entity\Cinema;
use App\Entity\VisualFormat;
use App\Factory\CinemaFactory;
use App\Factory\VisualFormatFactory;
use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Zenstruck\Foundry\Test\Factories;

class CinemaTest extends KernelTestCase
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


    public function testVisualFormatBecomesInactiveAfterRemovedFromCinemaCollection(): void {
        
        $cinema = CinemaFactory::random()->_real();
        $visualFormat = VisualFormatFactory::createOne([
            "cinema" => $cinema
        ])->_real();

        assert($visualFormat instanceof VisualFormat);
        assert($cinema instanceof Cinema);

        $this->assertTrue($visualFormat->isActive());

        $cinema->removeVisualFormat($visualFormat);

        $this->assertFalse($visualFormat->isActive());

    }

    protected function tearDown(): void
    {
        parent::tearDown();

        $this->entityManager->close();
        $this->entityManager = null;
    }
}
