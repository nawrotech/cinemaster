<?php

namespace App\Tests;

use App\Enum\LanguagePresentation;
use App\Factory\ScreeningFormatFactory;
use LogicException;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Zenstruck\Foundry\Test\Factories;

class ScreeningFormatTest extends KernelTestCase
{

    use Factories;

    public function testExceptionThrownWhenImmutableLanguagePresentationIsModified(): void {

        $screeningFormat = ScreeningFormatFactory::createOne([
            "languagePresentation" => LanguagePresentation::DUBBING
        ])->_real();

        $this->expectException(LogicException::class);
        $screeningFormat->setLanguagePresentation(LanguagePresentation::SUBTITLES);

    }

}
