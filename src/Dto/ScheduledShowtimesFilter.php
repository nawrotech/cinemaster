<?php

namespace App\Dto;

use Symfony\Component\Validator\Constraints as Assert;

class ScheduledShowtimesFilter
{
    public function __construct(
        #[Assert\Length(min: 0, max: 50)]
        public readonly string $screeningRoomName = "",

        #[Assert\Date]
        public readonly string $showtimeStartTime = "",

        #[Assert\Date]
        public readonly string $showtimeEndTime = "",

        #[Assert\Length(min: 0, max: 100)]
        public readonly string $movieTitle = "",

    ) {}
}
