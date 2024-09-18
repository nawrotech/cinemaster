<?php

namespace App\Entity;

use App\Repository\ScreeningRoomRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ScreeningRoomRepository::class)]
class ScreeningRoom
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\Column(length: 100)]
    private ?string $status = "available";

    /**
     * @var Collection<int, ScreeningRoomSeat>
     */
    #[ORM\OneToMany(targetEntity: ScreeningRoomSeat::class, mappedBy: 'ScreeningRoom')]
    private Collection $screeningRoomSeats;

    public function __construct()
    {
        $this->screeningRoomSeats = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

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
            $screeningRoomSeat->setScreeningRoom($this);
        }

        return $this;
    }

    public function removeScreeningRoomSeat(ScreeningRoomSeat $screeningRoomSeat): static
    {
        if ($this->screeningRoomSeats->removeElement($screeningRoomSeat)) {
            // set the owning side to null (unless already changed)
            if ($screeningRoomSeat->getScreeningRoom() === $this) {
                $screeningRoomSeat->setScreeningRoom(null);
            }
        }

        return $this;
    }
}
