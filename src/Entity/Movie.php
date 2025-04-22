<?php

namespace App\Entity;

use App\Contract\MovieInterface;
use App\Contract\SlugInterface;
use App\Repository\MovieRepository;
use App\Service\UploaderHelper;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\OrderBy;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

#[UniqueEntity(
    fields: ["title"],
    message: "Movie of that title already exists",
)]
#[ORM\Entity(repositoryClass: MovieRepository::class)]
class Movie implements MovieInterface, SlugInterface
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

    #[ORM\Column(length: 200, nullable: true)]
    private ?string $slug = null;

    /**
     * @var Collection<int, MovieScreeningFormat>
     */
    #[ORM\OneToMany(targetEntity: MovieScreeningFormat::class, mappedBy: 'movie', orphanRemoval: true)]
    private Collection $movieScreeningFormats;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $overview = null;

    #[ORM\Column(type: Types::DATE_IMMUTABLE, nullable: true)]
    private ?\DateTimeImmutable $releaseDate = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $posterFilename = null;

    #[ORM\ManyToOne(inversedBy: 'movies')]
    private ?Cinema $cinema = null;

    /**
     * @var Collection<int, MovieReference>
     */
    #[OrderBy(["position" => "ASC"])]
    #[ORM\OneToMany(targetEntity: MovieReference::class, mappedBy: 'movie')]
    private Collection $movieReferences;


    public function __construct()
    {
        $this->movieScreeningFormats = new ArrayCollection();
        $this->movieReferences = new ArrayCollection();
    }

    public function __toString(): string
    {
        return $this->title;
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

    public function setOverview(?string $overview): static
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

    public function getPosterFilename(): ?string
    {
        return $this->posterFilename;
    }

    public function setPosterFilename(?string $posterFilename): static
    {
        $this->posterFilename = $posterFilename;

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

    public function getPosterPath(): ?string {
        if (!$this->posterFilename) {
            return null;
        }
        return UploaderHelper::MOVIE_IMAGE . "/{$this->posterFilename}";
    }


    /**
     * @return Collection<int, MovieReference>
     */
    public function getMovieReferences(): Collection
    {
        return $this->movieReferences;
    }


    public function getSlug(): ?string
    {
        return $this->slug;
    }

    public function setSlug(string $slug): static  
    {
        $this->slug = $slug;

        return $this;
    }
}
