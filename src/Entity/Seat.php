<?php

namespace App\Entity;

use App\Repository\SeatRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
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

    /**
     * @var Collection<int, CinemaSeat>
     */
    #[ORM\OneToMany(targetEntity: CinemaSeat::class, mappedBy: 'seat')]
    private Collection $cinemaSeats;

    public function __construct()
    {
        $this->cinemaSeats = new ArrayCollection();
    }

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

    /**
     * @return Collection<int, CinemaSeat>
     */
    public function getCinemaSeats(): Collection
    {
        return $this->cinemaSeats;
    }

    public function addCinemaSeat(CinemaSeat $cinemaSeat): static
    {
        if (!$this->cinemaSeats->contains($cinemaSeat)) {
            $this->cinemaSeats->add($cinemaSeat);
            $cinemaSeat->setSeat($this);
        }

        return $this;
    }

    public function removeCinemaSeat(CinemaSeat $cinemaSeat): static
    {
        if ($this->cinemaSeats->removeElement($cinemaSeat)) {
            // set the owning side to null (unless already changed)
            if ($cinemaSeat->getSeat() === $this) {
                $cinemaSeat->setSeat(null);
            }
        }

        return $this;
    }
}
