<?php

namespace App\Tests;

use App\Factory\ScreeningRoomSetupFactory;
use Doctrine\ORM\EntityManagerInterface;
use RuntimeException;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Zenstruck\Foundry\Test\Factories;

class ScreeningRoomSetupTest extends KernelTestCase
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


    public function testExceptionThrownWhenImmutableSoundFormatIsModified(): void{
        
        $screeningRoomSetup = ScreeningRoomSetupFactory::createOne([
            "soundFormat" => "foo"
        ])->_real();

        $this->expectException(RuntimeException::class);
        $screeningRoomSetup->setSoundFormat("baz");

    }

   
    protected function tearDown(): void
    {
        parent::tearDown();

        $this->em->close();
        $this->em = null;
    }
}
