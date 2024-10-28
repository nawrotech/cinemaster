<?php

namespace App\Dto;

use App\Contracts\MovieInterface;

class TmdbMovieDto implements MovieInterface {
    public function __construct(
        public readonly int $id,
        public readonly string $title,
        public readonly string $overview,
        public readonly string $posterPath,
        public readonly string $releaseDate,
        public readonly int $durationInMinutes
    ) {}

    public static function fromResponse(array $data): self
    {
        return new self(
            $data['id'],
            $data['title'],
            $data['overview'],
            $data['poster_path'] ?? "",
            $data['release_date'],
            $data["runtime"]
        );
    }

    public function getId(): ?int {
        return $this->id;
    }   

    public function getPosterPath(): ?string {
        return "https://image.tmdb.org/t/p/w300{$this->posterPath}";
    }

    public function getTitle(): ?string {
        return $this->title;
    }

    public function getOverview(): ?string {
        return $this->overview;
    }

    public function getReleaseDate(): ?\DateTimeImmutable {
        return new \DateTimeImmutable($this->releaseDate);
    }

    public function getDurationInMinutes(): ?int {
        return $this->durationInMinutes;
    }

}