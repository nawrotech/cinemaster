<?php

namespace App\Entity;

use App\Repository\CinemaHistoryRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CinemaHistoryRepository::class)]
class CinemaHistory
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'cinemaHistories')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Cinema $cinema = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $changedAt = null;

    #[ORM\Column]
    private array $changes = [];

    public function getId(): ?int
    {
        return $this->id;
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


    public function getChangedAt(): ?\DateTimeImmutable
    {
        return $this->changedAt;
    }


    public function setChangedAt(?\DateTimeImmutable $changedAt): self
    {
        $this->changedAt = $changedAt;

        return $this;
    }

    public function getChanges(): array
    {
        return $this->changes;
    }

    public function setChanges(array $changes): static
    {
        $this->changes = $changes;

        return $this;
    }
}
