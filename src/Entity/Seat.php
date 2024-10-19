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
    private ?int $rowNum = null;

    #[ORM\Column]
    private ?int $seatNumInRow = null;


    public function getId(): ?int
    {
        return $this->id;
    }

    public function getSeatNumInRow(): ?int
    {
        return $this->seatNumInRow;
    }

    public function setSeatNumInRow(int $seatNumInRow): static
    {
        $this->seatNumInRow = $seatNumInRow;

        return $this;
    }

    public function getRowNum(): ?int
    {
        // chr(64 + $row) for A,B,C
        return $this->rowNum;
    }

    public function setRowNum(int $rowNum): static
    {
        $this->rowNum = $rowNum;

        return $this;
    }

    public function getSeatPosition() {
        return "{$this->rowNum}-{$this->seatNumInRow}";
    }


}