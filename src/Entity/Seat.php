<?php

namespace App\Entity;

use App\Repository\SeatRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: SeatRepository::class)]
class Seat
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?string $colNum = null;

    #[ORM\Column]
    private ?string $rowNum = null;

    #[ORM\Column(length: 100)]
    private ?string $type = "regular";

    #[ORM\ManyToOne(inversedBy: 'Seats')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Cinema $cinema = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getColNum(): ?string
    {
        return $this->colNum;
    }

    public function setColNum(string $colNum): static
    {
        $this->colNum = $colNum;

        return $this;
    }

    public function getRowNum(): ?string
    {
        return $this->rowNum;
    }

    public function setRowNum(string $rowNum): static
    {
        $this->rowNum = $rowNum;

        return $this;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(string $type): static
    {
        $this->type = $type;

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
