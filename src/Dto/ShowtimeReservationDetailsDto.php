<?php

namespace App\Dto;

class ShowtimeReservationDetailsDto {
    public function __construct(
        public readonly int $reservationId,
        public readonly string $movieTitle,
        public readonly string $screeningFormat,
        public readonly string $startsAt,
        public readonly string $endsAt,
        public readonly int $movieDurationInMinutes,
        public readonly int $durationInMinutes
    ) {}
}