<?php

namespace App\Tests;

use App\Entity\Cinema;
use App\Enum\LanguagePresentation;
use App\Factory\CinemaFactory;
use App\Factory\ScreeningFormatFactory;
use App\Factory\ScreeningRoomSetupFactory;
use App\Factory\VisualFormatFactory;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Zenstruck\Foundry\Test\Factories;

class CinemaTest extends KernelTestCase
{

    use Factories;

    public function testVisualFormatBecomesInactiveAfterRemovedFromCinemaCollection(): void {
        
        $cinema = CinemaFactory::random()->_real();
        $visualFormat = VisualFormatFactory::createOne([
            "cinema" => $cinema,
            "name" => "unique test name"
        ])->_real();

        $this->assertTrue($visualFormat->isActive());

        $cinema->removeVisualFormat($visualFormat);

        $this->assertFalse($visualFormat->isActive());

    }


    private function createCinemaAndVisualFormat(): array
    {
        $cinema = CinemaFactory::random()->_real();

        $visualFormat = VisualFormatFactory::createOne([
            "cinema" => $cinema,
            "name" => "unique test name"
        ])->_real();

        return [$cinema, $visualFormat];
    }


    public function testScreeningFormatBecomesInactiveAfterRemovedFromCinemaCollection(): void {
        [$cinema, $visualFormat] = $this->createCinemaAndVisualFormat();
        
        $screeningFormat = ScreeningFormatFactory::createOne([
            "cinema" => $cinema,
            "visualFormat" => $visualFormat,
            "languagePresentation" => LanguagePresentation::SUBTITLES
        ])->_real();

        $this->assertTrue($screeningFormat->isActive());

        $cinema->removeScreeningFormat($screeningFormat);
        $this->assertFalse($screeningFormat->isActive());

    }

    public function testScreeningRoomSetupBecomesInactiveAfterRemovedFromCinemaCollection(): void {
        [$cinema, $visualFormat] = $this->createCinemaAndVisualFormat();

        $screeningRoomSetup = ScreeningRoomSetupFactory::createOne([
            "cinema" => $cinema,
            "visualFormat" => $visualFormat
        ])->_real();

        assert($cinema instanceof Cinema);

        $this->assertTrue($screeningRoomSetup->isActive());

        $cinema->removeScreeningRoomSetup($screeningRoomSetup);

        $this->assertFalse($screeningRoomSetup->isActive());
    }


}
