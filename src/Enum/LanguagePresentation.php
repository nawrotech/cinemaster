<?php

namespace App\Enum;

use App\Traits\EnumToArrayTrait;

enum LanguagePresentation: string {
    use EnumToArrayTrait;

    case DUBBING = "dubbing";
    case ORIGINAL = "original";
    case SUBTITLES = "subtitles";
    
}