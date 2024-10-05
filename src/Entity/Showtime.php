<?php

namespace App\Entity;

use App\Repository\ShowtimeRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ShowtimeRepository::class)]
class Showtime
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'showtimes')]
    #[ORM\JoinColumn(nullable: false)]
    private ?ScreeningRoom $screeningRoom = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $startTime = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $endTime = null;

    #[ORM\Column]
    private ?int $price = null;

    #[ORM\Column]
    private ?int $advertisementTimeInMinutes = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?MovieMovieType $movieFormat = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getScreeningRoom(): ?ScreeningRoom
    {
        return $this->screeningRoom;
    }

    public function setScreeningRoom(?ScreeningRoom $screeningRoom): static
    {
        $this->screeningRoom = $screeningRoom;

        return $this;
    }

    public function getStartTime(): ?\DateTimeInterface
    {
        return $this->startTime;
    }

    public function setStartTime(\DateTimeInterface $startTime): static
    {
        $this->startTime = $startTime;

        return $this;
    }

    public function getEndTime(): ?\DateTimeInterface
    {
        return $this->endTime;
    }

    public function setEndTime(\DateTimeInterface $endTime): static
    {
        $this->endTime = $endTime;

        return $this;
    }

    public function getPrice(): ?int
    {
        return $this->price;
    }

    public function setPrice(int $price): static
    {
        $this->price = $price;

        return $this;
    }

    public function getAdvertisementTimeInMinutes(): ?int
    {
        return $this->advertisementTimeInMinutes;
    }

    public function setAdvertisementTimeInMinutes(int $advertisementTimeInMinutes): static
    {
        $this->advertisementTimeInMinutes = $advertisementTimeInMinutes;

        return $this;
    }

    public function getMovieFormat(): ?MovieMovieType
    {
        return $this->movieFormat;
    }

    public function setMovieFormat(?MovieMovieType $movieFormat): static
    {
        $this->movieFormat = $movieFormat;

        return $this;
    }
}
