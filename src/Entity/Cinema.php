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
    private ?int $rowsMax = null;

    #[ORM\Column]
    private ?int $seatsPerRowMax = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $updatedAt = null;



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

    /**
     * @var Collection<int, CinemaHistory>
     */
    #[ORM\OneToMany(targetEntity: CinemaHistory::class, mappedBy: 'cinema')]
    private Collection $cinemaHistories;





    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
        $this->updatedAt = new \DateTimeImmutable();

        $this->cinemaSeats = new ArrayCollection();
        $this->screeningRooms = new ArrayCollection();
        $this->cinemaHistories = new ArrayCollection();
    }

    #[PrePersist]
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



    /**
     * @return Collection<int, CinemaSeat>
     */
    public function getCinemaSeats(): Collection
    {

        return $this->cinemaSeats->matching(CinemaSeatRepository::activeSeatsCriterion());
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

    public function getRowsMax(): ?int
    {
        return $this->rowsMax;
    }

    public function setRowsMax(?int $rowsMax): static
    {
        $this->rowsMax = $rowsMax;

        return $this;
    }

    public function getSeatsPerRowMax(): ?int
    {
        return $this->seatsPerRowMax;
    }

    public function setSeatsPerRowMax(int $seatsPerRowMax): static
    {
        $this->seatsPerRowMax = $seatsPerRowMax;

        return $this;
    }
}
