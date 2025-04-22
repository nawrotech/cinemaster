<?php

namespace App\Dto;

use App\Entity\Movie;
use DateTimeInterface;
use Doctrine\Common\Collections\Collection;

class MovieDetailsDto
{
    private ?int $id = null;
    private ?string $slug = null;
    private ?string $title = null;
    private ?string $overview = null;
    private ?string $posterPath = null;
    private ?DateTimeInterface $releaseDate = null;
    private ?int $durationInMinutes = null;
    private ?Collection $movieReferences = null;

    public static function fromMovieAndApi(Movie $movie, ?object $apiData = null): self
    {
        $dto = new self();
        $dto->id = $movie->getId();
        $dto->slug = $movie->getSlug();
        $dto->title = $movie->getTitle() ?? ($apiData ? $apiData->getTitle() : null);
        $dto->overview = $movie->getOverview() ?? ($apiData ? $apiData->getOverview() : null);
        $dto->posterPath = $movie->getPosterPath() ?? ($apiData ? $apiData->getPosterPath() : null);
        $dto->releaseDate = $movie->getReleaseDate() ?? ($apiData ? $apiData->getReleaseDate() : null);
        $dto->durationInMinutes = $movie->getDurationInMinutes() ?? ($apiData ? $apiData->getDurationInMinutes() : null);
        $dto->movieReferences = $movie->getMovieReferences();
        
        return $dto;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function getOverview(): ?string
    {
        return $this->overview;
    }

    public function getSlug(): ?string
    {
        return $this->slug;
    }

    public function getPosterPath(): ?string
    {
        return $this->posterPath;
    }

    public function getReleaseDate(): ?DateTimeInterface
    {
        return $this->releaseDate;
    }

    public function getDurationInMinutes(): ?int
    {
        return $this->durationInMinutes;
    }

    public function getMovieReferences(): ?Collection
    {
        return $this->movieReferences;
    }
}