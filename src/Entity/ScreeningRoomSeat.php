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
    private ?string $seatStatus = "available";

    #[ORM\Column(length: 100)]
    private ?string $seatType = "regular";


    #[ORM\ManyToOne(inversedBy: 'screeningRoomSeats')]
    #[ORM\JoinColumn(nullable: false)]
    private ?ScreeningRoom $ScreeningRoom = null;

    #[ORM\ManyToOne(inversedBy: 'screeningRoomSeats')]
    #[ORM\JoinColumn(name: "cinema_id", referencedColumnName: "cinema_id")]
    #[ORM\JoinColumn(name: "seat_id", referencedColumnName: "seat_id")]
    private ?CinemaSeat $seat = null;


    public function getId(): ?int
    {
        return $this->id;
    }

    public function getSeatStatus(): ?string
    {
        return $this->seatStatus;
    }

    public function setSeatStatus(string $seatStatus): static
    {
        $this->seatStatus = $seatStatus;

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

    public function getSeatType(): ?string
    {
        return $this->seatType;
    }

    public function setSeatType(string $seatType): static
    {
        $this->seatType = $seatType;

        return $this;
    }

    public function getSeat(): ?CinemaSeat
    {
        return $this->seat;
    }

    public function setSeat(?CinemaSeat $seat): static
    {
        $this->seat = $seat;

        return $this;
    }
}
