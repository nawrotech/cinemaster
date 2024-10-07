<?php

namespace App\Validator;

use Symfony\Component\Validator\Constraint;

#[\Attribute]
class OverlappingShowtimeInSameScreeningRoom extends Constraint {

    public string $message = "Those shows are already scheduled 
                                for specified time frame: {{ overlaps }} ";
    
}