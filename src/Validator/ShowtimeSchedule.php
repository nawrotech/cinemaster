<?php

namespace App\Validator;

use Symfony\Component\Validator\Constraint;

#[\Attribute]
final class ShowtimeSchedule extends Constraint
{
    public string $message = 'The show does NOT fit within operating hours. Operating hours: {{ cinemaOperatingHours }}. Showtime duration: {{ showtimeDuration }}.';
}
