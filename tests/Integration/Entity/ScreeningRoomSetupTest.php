<?php

namespace App\Tests;

use App\Factory\ScreeningRoomSetupFactory;
use LogicException;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Zenstruck\Foundry\Test\Factories;

class ScreeningRoomSetupTest extends KernelTestCase
{

    use Factories;

    public function testExceptionThrownWhenImmutableSoundFormatIsModified(): void{
        
        $screeningRoomSetup = ScreeningRoomSetupFactory::createOne([
            "soundFormat" => "foo"
        ])->_real();

        $this->expectException(LogicException::class);
        $screeningRoomSetup->setSoundFormat("baz");

    }

   

}
