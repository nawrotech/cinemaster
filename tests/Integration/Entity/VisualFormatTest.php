<?php

namespace App\Tests;

use App\Factory\VisualFormatFactory;
use Doctrine\ORM\EntityManagerInterface;
use RuntimeException;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Zenstruck\Foundry\Test\Factories;

class VisualFormatTest extends KernelTestCase
{

    use Factories;
    private ?EntityManagerInterface $em;

    protected function setUp(): void
    {
        $kernel = self::bootKernel();
        $this->em = $kernel->getContainer()
            ->get('doctrine')
            ->getManager();
    }

    public function testExceptionThrownWhenImmutableNameIsModified(): void{

        $visualFormat = VisualFormatFactory::createOne([
            "name" => "foo"
        ])->_real();

        $this->expectException(RuntimeException::class);
        $visualFormat->setName("baz");

    }

   
    protected function tearDown(): void
    {
        parent::tearDown();

        $this->em->close();
        $this->em = null;
    }
}
