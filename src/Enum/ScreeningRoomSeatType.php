<?php

namespace App\Enum;

enum ScreeningRoomSeatType: string {
    case REGULAR = "regular";
    case HANDICAPPED = "handicapped";
    case DBOX = "dbox";
    case VIP = "vip";  
    
    public static function getValuesArray(): array {
       return array_column(ScreeningRoomSeatType::cases(), "value");
    }
}