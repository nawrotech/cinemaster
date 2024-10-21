<?php

namespace App\Enum;

enum LanguagePresentation: string {
    case DUBBING = "dubbing";
    case ORIGINAL = "original";
    case SUBTITLES = "subtitles";
}