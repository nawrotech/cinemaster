<?php

namespace App\Entity;

use App\Repository\MovieScreeningFormatRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\UniqueConstraint;

#[UniqueConstraint(
    fields: ["movie", "cinema", "screeningFormat"],
)]
#[ORM\Entity(repositoryClass: MovieScreeningFormatRepository::class)]
class MovieScreeningFormat
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'movieScreeningFormats')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Movie $movie = null;

    #[ORM\ManyToOne(inversedBy: "movieScreeningFormats")]
    #[ORM\JoinColumn(nullable: false)]
    private ?ScreeningFormat $screeningFormat = null;

    #[ORM\ManyToOne(inversedBy: 'movieScreeningFormats')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Cinema $cinema = null;

    

    /**
     * @var Collection<int, Showtime>
     */
    #[ORM\OneToMany(targetEntity: Showtime::class, mappedBy: "movieScreeningFormat")]
    #[ORM\JoinColumn(nullable: false)]
    private ?Collection $showtimes = null;

    public function __construct()
    {
        $this->showtimes = new ArrayCollection();

    }

    /**
     * @return Collection<int, ScreeningRoom>
     */
    public function getShowtimes(): Collection
    {
        return $this->showtimes;
    }


    public function getId(): ?int
    {
        return $this->id;
    }

    public function getMovie(): ?Movie
    {
        return $this->movie;
    }

    public function setMovie(?Movie $movie): static
    {
        $this->movie = $movie;

        return $this;
    }

    public function getScreeningFormat(): ?ScreeningFormat
    {
        return $this->screeningFormat;
    }

    public function setScreeningFormat(?ScreeningFormat $screeningFormat): static
    {
        $this->screeningFormat = $screeningFormat;

        return $this;
    }
    
    public function getDisplayMovieScreeningFormat() {
        return "Movie: {$this->movie->getTitle()},
                Duration: {$this->movie->getDurationInMinutes()} minutes,
                Screening format: {$this->screeningFormat->getVisualFormat()->getName()}, 
                                    {$this->screeningFormat->getLanguagePresentation()->value}";
    }

    public function getDisplayScreeningFormat() {
        return "{$this->movie->getTitle()} {$this->screeningFormat->getVisualFormat()->getName()} {$this->screeningFormat->getLanguagePresentation()->value}";
                
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
