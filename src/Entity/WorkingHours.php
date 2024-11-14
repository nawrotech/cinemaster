<?php

namespace App\Entity;

use App\Repository\WorkingHoursRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: WorkingHoursRepository::class)]
class WorkingHours
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::TIME_IMMUTABLE)]
    private ?\DateTimeImmutable $openTime = null;

    #[ORM\Column(type: Types::TIME_IMMUTABLE)]
    private ?\DateTimeImmutable $closeTime = null;

    #[ORM\ManyToOne(inversedBy: 'workingHours')]
    private ?Cinema $cinema = null;

    #[ORM\Column]
    private ?int $dayOfTheWeek = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getOpenTime(): ?\DateTimeImmutable
    {
        return $this->openTime;
    }

    public function setOpenTime(\DateTimeImmutable $openTime): static
    {
        $this->openTime = $openTime;

        return $this;
    }

    public function getCloseTime(): ?\DateTimeImmutable
    {
        return $this->closeTime;
    }

    public function setCloseTime(\DateTimeImmutable $closeTime): static
    {
        $this->closeTime = $closeTime;

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

    public function getDayOfTheWeek(): ?int
    {
        return $this->dayOfTheWeek;
    }

    public function setDayOfTheWeek(int $dayOfTheWeek): static
    {
        $this->dayOfTheWeek = $dayOfTheWeek;

        return $this;
    }
}
