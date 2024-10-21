<?php

namespace App\Enum;

enum ReservationSeatStatus: string {
    case LOCKED = "locked";
    case RESERVED = "reserved";
    case AVAILABLE = "available";

}