<?php

namespace App\Entity;

use App\Repository\ReservationRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ReservationRepository::class)]
class Reservation
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 50)]
    private ?string $email = null;

    
    #[ORM\ManyToOne(inversedBy: 'reservations')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Showtime $showtime = null;

    /**
     * @var Collection<int, ReservationSeat>
     */
    #[ORM\OneToMany(targetEntity: ReservationSeat::class, mappedBy: 'reservation')]
    private Collection $reservationSeats;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column]
    private ?bool $isValidated = false;

    public function __construct()
    {
        $this->reservationSeats = new ArrayCollection();
        $this->createdAt = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;

        return $this;
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

    /**
     * @return Collection<int, ReservationSeat>
     */
    public function getReservationSeats(): Collection
    {
        return $this->reservationSeats;
    }

    public function addReservationSeat(ReservationSeat $reservationSeat): static
    {
        if (!$this->reservationSeats->contains($reservationSeat)) {
            $this->reservationSeats->add($reservationSeat);
            $reservationSeat->setReservation($this);
        }

        return $this;
    }

    public function removeReservationSeat(ReservationSeat $reservationSeat): static
    {
        if ($this->reservationSeats->removeElement($reservationSeat)) {
            // set the owning side to null (unless already changed)
            if ($reservationSeat->getReservation() === $this) {
                $reservationSeat->setReservation(null);
            }
        }

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function isValidated(): ?bool
    {
        return $this->isValidated;
    }

    public function setValidated(bool $isValidated): static
    {
        $this->isValidated = $isValidated;

        return $this;
    }
}
