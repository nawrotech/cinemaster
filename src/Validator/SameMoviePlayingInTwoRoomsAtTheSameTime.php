<?php

namespace App\Validator;

use Symfony\Component\Validator\Constraint;

#[\Attribute]
class SameMoviePlayingInTwoRoomsAtTheSameTime extends Constraint {

    public string $message = "{{ movieTitle }} is playing in 
                                {{ roomName }} on {{ date }} between {{ startsAt }} and {{ endsAt }}";
     
    
}