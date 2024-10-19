<?php

namespace App\Entity;

use App\Repository\ReservationSeatRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ReservationSeatRepository::class)]
class ReservationSeat
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'reservationSeats')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Showtime $showtime = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?ScreeningRoomSeat $seat = null;

    #[ORM\Column(length: 15)]
    private ?string $status = "available";

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $statusLockedExpiresAt = null;

    #[ORM\ManyToOne(inversedBy: 'reservationSeats')]
    #[ORM\JoinColumn(nullable: true)]
    private ?Reservation $reservation = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $email = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getShowtime(): ?Showtime
    {
        return $this->showtime;
    }

    public function setShowtime(?Showtime $showtime): static
    {
        $this->showtime = $showtime;

        return $this;
    }

    public function getSeat(): ?ScreeningRoomSeat
    {
        return $this->seat;
    }

    public function setSeat(?ScreeningRoomSeat $seat): static
    {
        $this->seat = $seat;

        return $this;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(string $status): static
    {
        $this->status = $status;

        return $this;
    }

    public function getStatusLockedExpiresAt(): ?\DateTimeImmutable
    {
        return $this->statusLockedExpiresAt;
    }

    public function setStatusLockedExpiresAt(?\DateTimeImmutable $statusLockedExpiresAt): static
    {
        $this->statusLockedExpiresAt = $statusLockedExpiresAt;

        return $this;
    }

    public function getReservation(): ?Reservation
    {
        return $this->reservation;
    }

    public function setReservation(?Reservation $reservation): static
    {
        $this->reservation = $reservation;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(?string $email): static
    {
        $this->email = $email;

        return $this;
    }
}
