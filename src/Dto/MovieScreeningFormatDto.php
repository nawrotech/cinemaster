<?php

namespace App\Dto;

use App\Entity\MovieScreeningFormat;

class MovieScreeningFormatDto
{
    public int $id;
    public string $displayScreeningFormat;
    public bool $isScheduledShowtime;   

    public function __construct(int $id, string $displayScreeningFormat, bool $isScheduledShowtime)
    {
        $this->id = $id;
        $this->displayScreeningFormat = $displayScreeningFormat;
        $this->isScheduledShowtime = $isScheduledShowtime;  
    }   

    public static function fromEntity(MovieScreeningFormat $movieScreeningFormat): self
    {
        return new self(
            id: $movieScreeningFormat->getId(),
            displayScreeningFormat: $movieScreeningFormat->getScreeningFormat()->getDisplayScreeningFormat(),
            isScheduledShowtime: $movieScreeningFormat->getShowtimes()->count() > 0
        );
    }
}