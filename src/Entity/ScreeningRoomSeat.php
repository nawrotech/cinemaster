<?php

namespace App\Entity;

use App\Repository\ScreeningRoomSeatRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ScreeningRoomSeatRepository::class)]
class ScreeningRoomSeat
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 100)]
    private ?string $seat_status = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?Seat $seat = null;

    #[ORM\ManyToOne(inversedBy: 'screeningRoomSeats')]
    #[ORM\JoinColumn(nullable: false)]
    private ?ScreeningRoom $ScreeningRoom = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getSeatStatus(): ?string
    {
        return $this->seat_status;
    }

    public function setSeatStatus(string $seat_status): static
    {
        $this->seat_status = $seat_status;

        return $this;
    }

    public function getSeat(): ?Seat
    {
        return $this->seat;
    }

    public function setSeat(?Seat $seat): static
    {
        $this->seat = $seat;

        return $this;
    }

    public function getScreeningRoom(): ?ScreeningRoom
    {
        return $this->ScreeningRoom;
    }

    public function setScreeningRoom(?ScreeningRoom $ScreeningRoom): static
    {
        $this->ScreeningRoom = $ScreeningRoom;

        return $this;
    }
}
