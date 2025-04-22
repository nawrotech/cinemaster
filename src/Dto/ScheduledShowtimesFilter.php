<?php

namespace App\Dto;

use Symfony\Component\Validator\Constraints as Assert;

class ScheduledShowtimesFilter
{
    public const PUBLICATION_ALL = '';
    public const PUBLICATION_PUBLISHED = '1';
    public const PUBLICATION_UNPUBLISHED = '0';

    public function __construct(
        #[Assert\Length(min: 0, max: 50)]
        public readonly string $screeningRoomName = "",

        #[Assert\Date]
        public readonly string $showtimeStartTime = "",

        #[Assert\Date]
        public readonly string $showtimeEndTime = "",

        #[Assert\Length(min: 0, max: 100)]
        public readonly string $movieTitle = "",

        #[Assert\Choice(choices: [self::PUBLICATION_ALL, self::PUBLICATION_PUBLISHED, self::PUBLICATION_UNPUBLISHED])]
        public readonly string $published = self::PUBLICATION_ALL,
    ) {}
}
