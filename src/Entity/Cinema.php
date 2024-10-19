<?php

namespace App\Entity;

use App\Repository\CinemaRepository;
use App\Repository\CinemaSeatRepository;
use Cocur\Slugify\Slugify;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\HasLifecycleCallbacks;
use Doctrine\ORM\Mapping\PrePersist;
use Doctrine\ORM\Mapping\PreUpdate;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

#[UniqueEntity(
    fields: ["name"],
    message: "Name of this room is alredy taken",
)]
#[HasLifecycleCallbacks]
#[ORM\Entity(repositoryClass: CinemaRepository::class)]
class Cinema
{
    
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 50, unique: true)]
    private ?string $name = null;

    #[ORM\Column(length: 100, unique: true)]
    private ?string $slug = null;

    #[ORM\Column]
    private ?int $maxRows = null;

    #[ORM\Column]
    private ?int $maxSeatsPerRow = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $updatedAt = null;

    /**
     * @var Collection<int, ScreeningRoom>
     */
    #[ORM\OneToMany(targetEntity: ScreeningRoom::class, mappedBy: 'cinema')]
    private Collection $screeningRooms;

    /**
     * @var Collection<int, CinemaHistory>
     */
    #[ORM\OneToMany(targetEntity: CinemaHistory::class, mappedBy: 'cinema')]
    private Collection $cinemaHistories;

    /**
     * @var Collection<int, Showtime>
     */
    #[ORM\OneToMany(targetEntity: Showtime::class, mappedBy: 'cinema')]
    private Collection $showtimes;


    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
        $this->updatedAt = new \DateTimeImmutable();
        $this->screeningRooms = new ArrayCollection();
        $this->cinemaHistories = new ArrayCollection();
        $this->showtimes = new ArrayCollection();
    }

    #[PrePersist]
    public function createSlug(): static
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

    public function getSlug(): ?string
    {
        return $this->slug;
    }

    public function setSlug(?string $slug): static
    {
        $this->slug = $slug;

        return $this;
    }



    public function getUpdatedAt(): \DateTimeImmutable
    {
        return $this->updatedAt;
    }


    public function setUpdatedAt(\DateTimeImmutable $updatedAt): self
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }


    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }


    public function setCreatedAt(\DateTimeImmutable $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * @return Collection<int, CinemaHistory>
     */
    public function getCinemaHistories(): Collection
    {
        return $this->cinemaHistories;
    }

    public function addCinemaHistory(CinemaHistory $cinemaHistory): static
    {
        if (!$this->cinemaHistories->contains($cinemaHistory)) {
            $this->cinemaHistories->add($cinemaHistory);
            $cinemaHistory->setCinema($this);
        }

        return $this;
    }

    public function removeCinemaHistory(CinemaHistory $cinemaHistory): static
    {
        if ($this->cinemaHistories->removeElement($cinemaHistory)) {
            // set the owning side to null (unless already changed)
            if ($cinemaHistory->getCinema() === $this) {
                $cinemaHistory->setCinema(null);
            }
        }

        return $this;
    }

    public function getMaxRows(): ?int
    {
        return $this->maxRows;
    }

    public function setMaxRows(?int $maxRows): static
    {
        $this->maxRows = $maxRows;

        return $this;
    }

    public function getMaxSeatsPerRow(): ?int
    {
        return $this->maxSeatsPerRow;
    }

    public function setMaxSeatsPerRow(int $maxSeatsPerRow): static
    {
        $this->maxSeatsPerRow = $maxSeatsPerRow;

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
            $showtime->setCinema($this);
        }

        return $this;
    }

    public function removeShowtime(Showtime $showtime): static
    {
        if ($this->showtimes->removeElement($showtime)) {
            // set the owning side to null (unless already changed)
            if ($showtime->getCinema() === $this) {
                $showtime->setCinema(null);
            }
        }

        return $this;
    }
}
