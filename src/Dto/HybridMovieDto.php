<?php

namespace App\Dto;

use App\Contracts\MovieInterface;
use App\Entity\Movie;

class HybridMovieDto implements MovieInterface {
    public function __construct(
        public readonly int $id,
        public readonly ?int $tmdbId,
        public readonly string $title,
        public readonly string $overview,
        public readonly string $posterPath,
        public readonly string $durationInMinutes,
        public readonly string $releaseDate
    ) {}

 
    public static function create(
        Movie $movie, 
        ?TmdbMovieDto $tmdbData = null
    ): self {
        return new self(
            id: $movie->getId(),
            tmdbId: $movie->getTmdbId(),
            title: $movie->getTitle(),
            overview: $movie->getOverview() ?? $tmdbData?->overview ?? '',
            posterPath: $movie->getPosterPath() ?? $tmdbData?->posterPath ?? '',
            durationInMinutes: $movie->getDurationInMinutes() ?? $tmdbData?->durationInMinutes ?? '',
            releaseDate: $movie->getReleaseDate() ?? $tmdbData?->durationInMinutes ?? '',
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