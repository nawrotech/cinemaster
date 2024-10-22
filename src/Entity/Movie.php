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
     * @var Collection<int, MovieScreeningFormat>
     */
    #[ORM\OneToMany(targetEntity: MovieScreeningFormat::class, mappedBy: 'movie')]
    private Collection $movieScreeningFormats;

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

  



   


}
