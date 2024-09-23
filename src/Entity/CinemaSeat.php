<?php

namespace App\Entity;

use App\Repository\CinemaSeatRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CinemaSeatRepository::class)]
class CinemaSeat
{

    #[ORM\Id, ORM\ManyToOne(inversedBy: 'cinemaSeats')]
    private ?Cinema $cinema = null;

    #[ORM\Id, ORM\ManyToOne(inversedBy: 'cinemaSeats')]
    private ?Seat $seat = null;


    /**
     * @var Collection<int, ScreeningRoomSeat>
     */
    #[ORM\OneToMany(targetEntity: ScreeningRoomSeat::class, mappedBy: 'seat')]
    private Collection $screeningRoomSeats;

    #[ORM\Column(length: 15)]
    private ?string $status = "active";

    public function __construct()
    {
        $this->screeningRoomSeats = new ArrayCollection();
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

    public function getSeat(): ?Seat
    {
        return $this->seat;
    }

    public function setSeat(?Seat $seat): static
    {
        $this->seat = $seat;

        return $this;
    }

    /**
     * @return Collection<int, ScreeningRoomSeat>
     */
    public function getScreeningRoomSeats(): Collection
    {
        return $this->screeningRoomSeats;
    }

    public function addScreeningRoomSeat(ScreeningRoomSeat $screeningRoomSeat): static
    {
        if (!$this->screeningRoomSeats->contains($screeningRoomSeat)) {
            $this->screeningRoomSeats->add($screeningRoomSeat);
            $screeningRoomSeat->setSeat($this);
        }

        return $this;
    }

    public function removeScreeningRoomSeat(ScreeningRoomSeat $screeningRoomSeat): static
    {
        if ($this->screeningRoomSeats->removeElement($screeningRoomSeat)) {
            // set the owning side to null (unless already changed)
            if ($screeningRoomSeat->getSeat() === $this) {
                $screeningRoomSeat->setSeat(null);
            }
        }

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
}
