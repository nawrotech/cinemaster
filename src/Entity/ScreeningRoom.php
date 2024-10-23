<?php

namespace App\Entity;

use App\Repository\ScreeningRoomRepository;
use Cocur\Slugify\Slugify;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\HasLifecycleCallbacks;
use Doctrine\ORM\Mapping\PreFlush;
use Doctrine\ORM\Mapping\PreUpdate;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

#[UniqueEntity(
    fields: ["name"],
    message: "Name of this room is alredy taken",
)]
#[HasLifecycleCallbacks]
#[ORM\Entity(repositoryClass: ScreeningRoomRepository::class)]
class ScreeningRoom
{

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 50, unique: true)]
    private ?string $name = null;

    #[ORM\Column(length: 100, unique: true)]
    private ?string $slug = null;

    #[ORM\Column(length: 100)]
    private ?string $status = "available";

    /**
     * @var Collection<int, ScreeningRoomSeat>
     */
    #[ORM\OneToMany(targetEntity: ScreeningRoomSeat::class, mappedBy: 'screeningRoom')]
    private Collection $screeningRoomSeats;

    #[ORM\ManyToOne(inversedBy: 'screeningRooms')]
    private ?Cinema $cinema = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $updatedAt = null;

    #[ORM\Column(nullable: true)]
    private ?int $maintenanceTimeInMinutes = null;
    
    /**
     * @var Collection<int, Showtime>
     */
    #[ORM\OneToMany(targetEntity: Showtime::class, mappedBy: 'screeningRoom')]
    private Collection $showtimes;

    #[ORM\ManyToOne(inversedBy: 'screeningRooms')]
    #[ORM\JoinColumn(nullable: true, onDelete: 'SET NULL')] 
    private ?ScreeningRoomSetup $screeningRoomSetup = null;

    public function __construct()
    {

        $this->createdAt = new \DateTimeImmutable();
        $this->updatedAt = new \DateTimeImmutable();
        $this->screeningRoomSeats = new ArrayCollection();
        $this->showtimes = new ArrayCollection();
    }

    #[PreFlush]
    public function createSlugAndCreationDates(): static
    {
        $slugify = new Slugify();
        $this->slug = $slugify->slugify($this->name);
        return $this;
    }

    #[PreUpdate]
    public function updateUpdatedAt(): static
    {
        $slugify = new Slugify();
        $this->updatedAt = new \DateTimeImmutable();
        $this->slug = $slugify->slugify($this->name);
        return $this;
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

    public function getSlug(): ?string
    {
        return $this->slug;
    }

    public function setSlug(?string $slug): static
    {
        $this->slug = $slug;

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

    public function getCinema(): ?Cinema
    {
        return $this->cinema;
    }

    public function setCinema(?Cinema $cinema): static
    {
        $this->cinema = $cinema;

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

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(\DateTimeImmutable $updatedAt): static
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    /**
     * @return Collection<int, Showtime>
     */
    public function getShowtimes(): Collection
    {
        return $this->showtimes;
    }

    public function addShowtime(Showtime $showtime): static
    {
        if (!$this->showtimes->contains($showtime)) {
            $this->showtimes->add($showtime);
            $showtime->setScreeningRoom($this);
        }

        return $this;
    }

    public function removeShowtime(Showtime $showtime): static
    {
        if ($this->showtimes->removeElement($showtime)) {
            // set the owning side to null (unless already changed)
            if ($showtime->getScreeningRoom() === $this) {
                $showtime->setScreeningRoom(null);
            }
        }

        return $this;
    }

    public function getMaintenanceTimeInMinutes(): ?int
    {
        return $this->maintenanceTimeInMinutes;
    }

    public function setMaintenanceTimeInMinutes(?int $maintenanceTimeInMinutes): static
    {
        $this->maintenanceTimeInMinutes = $maintenanceTimeInMinutes;

        return $this;
    }

    public function getScreeningRoomSetup(): ?ScreeningRoomSetup
    {
        return $this->screeningRoomSetup;
    }

    public function setScreeningRoomSetup(?ScreeningRoomSetup $screeningRoomSetup): static
    {
        $this->screeningRoomSetup = $screeningRoomSetup;

        return $this;
    }

 
}
