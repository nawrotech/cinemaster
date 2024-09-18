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
    private ?int $row_number = null;

    #[ORM\Column]
    private ?int $column_number = null;

    #[ORM\Column(length: 255)]
    private ?string $type = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getRowNumber(): ?int
    {
        return $this->row_number;
    }

    public function setRowNumber(int $row_number): static
    {
        $this->row_number = $row_number;

        return $this;
    }

    public function getColumnNumber(): ?int
    {
        return $this->column_number;
    }

    public function setColumnNumber(int $column_number): static
    {
        $this->column_number = $column_number;

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
}
