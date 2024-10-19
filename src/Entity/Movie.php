<?php

namespace App\Entity;

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
class Movie
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 100)]
    private ?string $title = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $description = null;

    #[ORM\Column]
    private ?int $durationInMinutes = null;

    /**
     * @var Collection<int, MovieFormat>
     */
    #[ORM\OneToMany(targetEntity: MovieFormat::class, mappedBy: 'movie')]
    private Collection $movieFormats;

    public function __construct()
    {
        $this->movieFormats = new ArrayCollection();
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

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): static
    {
        $this->description = $description;

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
     * @return Collection<int, MovieFormat>
     */
    public function getMovieFormats(): Collection
    {
        return $this->movieFormats;
    }

    public function addMovieFormat(MovieFormat $movieFormat): static
    {
        if (!$this->movieFormats->contains($movieFormat)) {
            $this->movieFormats->add($movieFormat);
            $movieFormat->setMovie($this);
        }

        return $this;
    }

    public function removeMovieFormat(MovieFormat $movieFormat): static
    {
        if ($this->movieFormats->removeElement($movieFormat)) {
            // set the owning side to null (unless already changed)
            if ($movieFormat->getMovie() === $this) {
                $movieFormat->setMovie(null);
            }
        }

        return $this;
    }

  



   


}
