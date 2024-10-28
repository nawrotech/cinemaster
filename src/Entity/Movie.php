<?php

namespace App\Entity;

use App\Contracts\MovieInterface;
use App\Repository\MovieRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

#[UniqueEntity(
    fields: ["title"],
    message: "Movie of that title already exists",
)]
#[ORM\Entity(repositoryClass: MovieRepository::class)]
class Movie implements MovieInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(nullable: true)]
    private ?int $tmdbId = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $title = null;

    #[ORM\Column(nullable: true)]
    private ?int $durationInMinutes = null;

    /**
     * @var Collection<int, MovieScreeningFormat>
     */
    #[ORM\OneToMany(targetEntity: MovieScreeningFormat::class, mappedBy: 'movie')]
    private Collection $movieScreeningFormats;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $overview = null;

    #[ORM\Column(type: Types::DATE_IMMUTABLE, nullable: true)]
    private ?\DateTimeImmutable $releaseDate = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $posterPath = null;

    #[ORM\ManyToOne(inversedBy: 'movies')]
    private ?Cinema $cinema = null;

    public function __construct()
    {
        $this->movieScreeningFormats = new ArrayCollection();
    }


    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): static
    {
        $this->title = $title;

        return $this;
    }


    public function getDurationInMinutes(): ?int
    {
        return $this->durationInMinutes;
    }

    public function setDurationInMinutes(int $durationInMinutes): static
    {
        $this->durationInMinutes = $durationInMinutes;

        return $this;
    }

    /**
     * @return Collection<int, MovieScreeningFormat>
     */
    public function getMovieScreeningFormats(): Collection
    {
        return $this->movieScreeningFormats;
    }

    public function addMovieScreeningFormat(MovieScreeningFormat $movieScreeningFormat): static
    {
        if (!$this->movieScreeningFormats->contains($movieScreeningFormat)) {
            $this->movieScreeningFormats->add($movieScreeningFormat);
            $movieScreeningFormat->setMovie($this);
        }

        return $this;
    }

    public function removeMovieScreeningFormat(MovieScreeningFormat $movieScreeningFormat): static
    {
        if ($this->movieScreeningFormats->removeElement($movieScreeningFormat)) {
            // set the owning side to null (unless already changed)
            if ($movieScreeningFormat->getMovie() === $this) {
                $movieScreeningFormat->setMovie(null);
            }
        }

        return $this;
    }

    public function getTmdbId(): ?int
    {
        return $this->tmdbId;
    }

    public function setTmdbId(?int $tmdbId): static
    {
        $this->tmdbId = $tmdbId;

        return $this;
    }

    public function getOverview(): ?string
    {
        return $this->overview;
    }

    public function setOverview(string $overview): static
    {
        $this->overview = $overview;

        return $this;
    }

    public function getReleaseDate(): ?\DateTimeImmutable
    {
        return $this->releaseDate;
    }

    public function setReleaseDate(\DateTimeImmutable $releaseDate): static
    {
        $this->releaseDate = $releaseDate;

        return $this;
    }

    public function getPosterPath(): ?string
    {
        return $this->posterPath;
    }

    public function setPosterPath(?string $posterPath): static
    {
        $this->posterPath = $posterPath;

        return $this;
    }

    public function getCinema(): ?Cinema
    {
        return $this->cinema;
    }

    public function setCinema(?Cinema $cinema): static
    {
        $this->cinema = $cinema;

        return $this;
    }

  



   


}
