<?php

namespace App\Dto;

use App\Contract\MovieInterface;

class TmdbMovieDto implements MovieInterface {
    private int $id;
    private string $title;
    private string $overview;
    private string $posterPath;
    private \DateTimeImmutable $releaseDate;
    private int $durationInMinutes;

    public function __construct(
        int $id,
        string $title,
        string $overview,
        string $posterPath,
        string $releaseDate,
        int $durationInMinutes
    ) {
        $this->id = $id;
        $this->title = $title;
        $this->overview = $overview;
        $this->posterPath = $this->buildPosterUrl($posterPath);
        $this->releaseDate = new \DateTimeImmutable($releaseDate);
        $this->durationInMinutes = $durationInMinutes;
    }
    
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

    private function buildPosterUrl(string $posterPath): string
    {
        return "https://image.tmdb.org/t/p/w300$posterPath";
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function getOverview(): string
    {
        return $this->overview;
    }

    public function getPosterPath(): string
    {
        return $this->posterPath;
    }

    public function getReleaseDate(): \DateTimeImmutable
    {
        return $this->releaseDate;
    }

    public function getDurationInMinutes(): int
    {
        return $this->durationInMinutes;
    }

}