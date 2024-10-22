<?php

namespace App\Traits;

trait EnumToArrayTrait {
    public static function getValuesArray(): array {
        return array_column(static::cases(), 'value');
    }
}

