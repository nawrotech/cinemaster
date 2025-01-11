<?php

namespace App\Tests;

use App\Entity\Cinema;
use App\Entity\VisualFormat;
use App\Enum\LanguagePresentation;
use App\Factory\CinemaFactory;
use App\Factory\ScreeningFormatFactory;
use App\Factory\ScreeningRoomSetupFactory;
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


        $this->assertTrue($visualFormat->isActive());

        $cinema->removeVisualFormat($visualFormat);

        $this->assertFalse($visualFormat->isActive());

    }

    public function testScreeningFormatBecomesInactiveAfterRemovedFromCinemaCollection(): void {
        $cinema = CinemaFactory::random()->_real();

        $visualFormat = VisualFormatFactory::createOne([
            "cinema" => $cinema
        ])->_real();

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
        $cinema = CinemaFactory::random()->_real();

        $visualFormat = VisualFormatFactory::createOne([
            "cinema" => $cinema
        ])->_real();

        $screeningRoomSetup = ScreeningRoomSetupFactory::createOne([
            "cinema" => $cinema,
            "visualFormat" => $visualFormat
        ])->_real();

        assert($cinema instanceof Cinema);

        $this->assertTrue($screeningRoomSetup->isActive());

        $cinema->removeScreeningRoomSetup($screeningRoomSetup);

        $this->assertFalse($screeningRoomSetup->isActive());


    }

    protected function tearDown(): void
    {
        parent::tearDown();

        $this->entityManager->close();
        $this->entityManager = null;
    }
}
