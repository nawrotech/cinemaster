<?php

namespace App\Traits;

use Symfony\Component\String\AbstractUnicodeString;
use Symfony\Component\String\Slugger\AsciiSlugger;

trait SlugTrait {
    public function generateSlug(string $value): string {
        $slugger = new AsciiSlugger();
        return $this->slug = $slugger->slug($value)->lower();
    }
}