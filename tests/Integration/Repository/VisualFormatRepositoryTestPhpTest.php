<?php

namespace App\Tests;

use App\Factory\CinemaFactory;
use App\Factory\VisualFormatFactory;
use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Zenstruck\Foundry\Test\Factories;

class VisualFormatRepositoryTestPhpTest extends KernelTestCase
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


    public function testVisualFormatsAreActiveOnCreation(): void
    {
        $kernel = self::bootKernel();
        $this->assertSame('test', $kernel->getEnvironment());

        $visualFormat = VisualFormatFactory::createOne([
            "cinema" => CinemaFactory::random()
        ]);

        $this->assertTrue($visualFormat->isActive());
    }

    
}
