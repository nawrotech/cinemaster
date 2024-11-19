<?php

namespace App\Dto;

use App\Entity\Showtime;

class ShowtimeDto {
        public function __construct(
            public readonly int $id,
            public readonly string $movieTitle,
            public readonly string $screeningFormat,
            public readonly string $startsAt,
            public readonly string $endsAt,
            public readonly int $movieDurationInMinutes,
            public readonly int $durationInMinutes
        ) {}
    
        public static function fromEntity(Showtime $showtime): self {
            return new self(
                id: $showtime->getId(),
                movieTitle: $showtime->getMovieScreeningFormat()->getMovie()->getTitle(),
                screeningFormat: $showtime->getMovieScreeningFormat()->getScreeningFormat()->getDisplayScreeningFormat(),
                startsAt: $showtime->getStartsAt()->format(\DateTime::ATOM),
                endsAt: $showtime->getEndsAt()->format(\DateTime::ATOM),
                movieDurationInMinutes: $showtime->getMovieScreeningFormat()->getMovie()->getDurationInMinutes(),
                durationInMinutes: $showtime->getDuration()
            );
        }
    
}