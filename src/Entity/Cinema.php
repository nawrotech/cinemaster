<?php

namespace App\Entity;

use App\Repository\CinemaRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CinemaRepository::class)]
class Cinema
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    /**
     * @var Collection<int, CinemaSeat>
     */
    #[ORM\OneToMany(targetEntity: CinemaSeat::class, mappedBy: 'cinema')]
    private Collection $cinemaSeats;

    /**
     * @var Collection<int, ScreeningRoom>
     */
    #[ORM\OneToMany(targetEntity: ScreeningRoom::class, mappedBy: 'cinema')]
    private Collection $screeningRooms;

    public function __construct()
    {
        $this->cinemaSeats = new ArrayCollection();
        $this->screeningRooms = new ArrayCollection();
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
            $cinemaSeat->setCinema($this);
        }

        return $this;
    }

    public function removeCinemaSeat(CinemaSeat $cinemaSeat): static
    {
        if ($this->cinemaSeats->removeElement($cinemaSeat)) {
            // set the owning side to null (unless already changed)
            if ($cinemaSeat->getCinema() === $this) {
                $cinemaSeat->setCinema(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, ScreeningRoom>
     */
    public function getScreeningRooms(): Collection
    {
        return $this->screeningRooms;
    }

    public function addScreeningRoom(ScreeningRoom $screeningRoom): static
    {
        if (!$this->screeningRooms->contains($screeningRoom)) {
            $this->screeningRooms->add($screeningRoom);
            $screeningRoom->setCinema($this);
        }

        return $this;
    }

    public function removeScreeningRoom(ScreeningRoom $screeningRoom): static
    {
        if ($this->screeningRooms->removeElement($screeningRoom)) {
            // set the owning side to null (unless already changed)
            if ($screeningRoom->getCinema() === $this) {
                $screeningRoom->setCinema(null);
            }
        }

        return $this;
    }
}
