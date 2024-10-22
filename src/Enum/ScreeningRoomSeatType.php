<?php

namespace App\Enum;

use App\Traits\EnumToArrayTrait;

enum ScreeningRoomSeatType: string {
    use EnumToArrayTrait;

    case REGULAR = "regular";
    case HANDICAPPED = "handicapped";
    case DBOX = "dbox";
    case VIP = "vip";  
    

}