<?php

namespace App\Tests;

use App\Enum\LanguagePresentation;
use App\Factory\ScreeningFormatFactory;
use Doctrine\ORM\EntityManager;
use RuntimeException;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Zenstruck\Foundry\Test\Factories;

class ScreeningFormatTest extends KernelTestCase
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

    public function testExceptionThrownWhenImmutableLanguagePresentationIsModified(): void {

        $screeningFormat = ScreeningFormatFactory::createOne([
            "languagePresentation" => LanguagePresentation::DUBBING
        ])->_real();

        $this->expectException(RuntimeException::class);
        $screeningFormat->setLanguagePresentation(LanguagePresentation::SUBTITLES);

    }
   
    protected function tearDown(): void
    {
        parent::tearDown();

        $this->entityManager->close();
        $this->entityManager = null;
    }
}
