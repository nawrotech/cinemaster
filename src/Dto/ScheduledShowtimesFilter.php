<?php

namespace App\Dto;

use Symfony\Component\Validator\Constraints as Assert;

class ScheduledShowtimesFilter
{
    public const ALL = '';
    public const PUBLISHED = '1';
    public const UNPUBLISHED = '0';

    public function __construct(
        #[Assert\Length(min: 0, max: 50)]
        public readonly string $screeningRoomName = "",

        #[Assert\Date]
        public readonly string $showtimeStartsFrom = "",

        #[Assert\Date]
        public readonly string $showtimeStartsBefore = "",

        #[Assert\Length(min: 0, max: 100)]
        public readonly string $movieTitle = "",

        #[Assert\Choice(choices: [self::ALL, self::PUBLISHED, self::UNPUBLISHED])]
        public readonly string $published = self::ALL,

        #[Assert\Positive]
        public readonly int $page = 1,
    ) {}

    public function getPublishedAsBool(): ?bool
    {
        return match($this->published) {
            self::PUBLISHED => true,
            self::UNPUBLISHED => false,
            default => null,
        };
    }
}
