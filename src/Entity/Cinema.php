<?php

namespace App\Entity;

use App\Repository\CinemaRepository;
use App\Traits\SlugTrait;
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
    use SlugTrait;
    
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
     * @var Collection<int, Showtime>
     */
    #[ORM\OneToMany(targetEntity: Showtime::class, mappedBy: 'cinema')]
    private Collection $showtimes;

    #[ORM\Column(length: 50)]
    private ?string $streetName = null;

    #[ORM\Column(length: 10)]
    private ?string $buildingNumber = null;

    #[ORM\Column(length: 6)]
    private ?string $postalCode = null;

    #[ORM\Column(length: 25)]
    private ?string $city = null;

    #[ORM\Column(length: 30)]
    private ?string $district = null;

    #[ORM\Column(length: 50)]
    private ?string $country = null;

    #[ORM\ManyToOne(inversedBy: 'cinemas')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $owner = null;

    /**
     * @var Collection<int, VisualFormat>
     */
    #[ORM\OneToMany(targetEntity: VisualFormat::class, mappedBy: 'cinema', cascade: ["persist"])]
    private Collection $visualFormats;

    /**
     * @var Collection<int, ScreeningRoomSetup>
     */
    #[ORM\OneToMany(targetEntity: ScreeningRoomSetup::class, mappedBy: 'cinema', cascade: ["persist"])]
    private Collection $screeningRoomSetups;

    /**
     * @var Collection<int, ScreeningFormat>
     */
    #[ORM\OneToMany(targetEntity: ScreeningFormat::class, mappedBy: 'cinema', cascade: ["persist"])]
    private Collection $screeningFormats;

    /**
     * @var Collection<int, MovieScreeningFormat>
     */
    #[ORM\OneToMany(targetEntity: MovieScreeningFormat::class, mappedBy: 'cinema')]
    private Collection $movieScreeningFormats;

    /**
     * @var Collection<int, Movie>
     */
    #[ORM\OneToMany(targetEntity: Movie::class, mappedBy: 'cinema')]
    private Collection $movies;


    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
        $this->updatedAt = new \DateTimeImmutable();
        $this->screeningRooms = new ArrayCollection();
        $this->showtimes = new ArrayCollection();
        $this->visualFormats = new ArrayCollection();
        $this->screeningRoomSetups = new ArrayCollection();
        $this->screeningFormats = new ArrayCollection();
        $this->movieScreeningFormats = new ArrayCollection();
        $this->movies = new ArrayCollection();
    }

    #[PrePersist]
    public function createSlug(): static
    {
        $this->slug = $this->generateSlug($this->name);
        return $this;
    }

    #[PreUpdate]
    public function updateUpdatedAt(): static
    {
        $this->updatedAt = new \DateTimeImmutable();
        $this->slug = $this->generateSlug($this->name);
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

    public function getStreetName(): ?string
    {
        return $this->streetName;
    }

    public function setStreetName(string $streetName): static
    {
        $this->streetName = $streetName;

        return $this;
    }

    public function getBuildingNumber(): ?string
    {
        return $this->buildingNumber;
    }

    public function setBuildingNumber(string $buildingNumber): static
    {
        $this->buildingNumber = $buildingNumber;

        return $this;
    }

    public function getPostalCode(): ?string
    {
        return $this->postalCode;
    }

    public function setPostalCode(string $postalCode): static
    {
        $this->postalCode = $postalCode;

        return $this;
    }

    public function getCity(): ?string
    {
        return $this->city;
    }

    public function setCity(string $city): static
    {
        $this->city = $city;

        return $this;
    }

    public function getDistrict(): ?string
    {
        return $this->district;
    }

    public function setDistrict(string $district): static
    {
        $this->district = $district;

        return $this;
    }

    public function getCountry(): ?string
    {
        return $this->country;
    }

    public function setCountry(string $country): static
    {
        $this->country = $country;

        return $this;
    }

    public function getOwner(): ?User
    {
        return $this->owner;
    }

    public function setOwner(?User $owner): static
    {
        $this->owner = $owner;

        return $this;
    }

    /**
     * @return Collection<int, VisualFormat>
     */
    public function getVisualFormats(): Collection
    {
        return $this->visualFormats;
    }

    public function addVisualFormat(VisualFormat $visualFormat): static
    {
        if (!$this->visualFormats->contains($visualFormat)) {
            $this->visualFormats->add($visualFormat);
            $visualFormat->setCinema($this);
        }

        return $this;
    }

    public function removeVisualFormat(VisualFormat $visualFormat): static
    {
        if ($this->visualFormats->removeElement($visualFormat)) {
            if ($visualFormat->getCinema() === $this) {
                $visualFormat->setActive(false);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, ScreeningRoomSetup>
     */
    public function getScreeningRoomSetups(): Collection
    {
        return $this->screeningRoomSetups;
    }

    public function addScreeningRoomSetup(ScreeningRoomSetup $screeningRoomSetup): static
    {
        if (!$this->screeningRoomSetups->contains($screeningRoomSetup)) {
            $this->screeningRoomSetups->add($screeningRoomSetup);
            $screeningRoomSetup->setCinema($this);
        }

        return $this;
    }

    public function removeScreeningRoomSetup(ScreeningRoomSetup $screeningRoomSetup): static
    {
        if ($this->screeningRoomSetups->removeElement($screeningRoomSetup)) {
            if ($screeningRoomSetup->getCinema() === $this) {
                $screeningRoomSetup->setActive(false);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, ScreeningFormat>
     */
    public function getScreeningFormats(): Collection
    {
        return $this->screeningFormats;
    }

    public function addScreeningFormat(ScreeningFormat $screeningFormat): static
    {
        if (!$this->screeningFormats->contains($screeningFormat)) {
            $this->screeningFormats->add($screeningFormat);
            $screeningFormat->setCinema($this);
        }

        return $this;
    }

    public function removeScreeningFormat(ScreeningFormat $screeningFormat): static
    {
        if ($this->screeningFormats->removeElement($screeningFormat)) {
            if ($screeningFormat->getCinema() === $this) {
                $screeningFormat->setActive(false);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, MovieScreeningFormat>
     */
    public function getMovieScreeningFormats(): Collection
    {
        return $this->movieScreeningFormats;
    }

    public function addMovieScreeningFormat(MovieScreeningFormat $movieScreeningFormat): static
    {
        if (!$this->movieScreeningFormats->contains($movieScreeningFormat)) {
            $this->movieScreeningFormats->add($movieScreeningFormat);
            $movieScreeningFormat->setCinema($this);
        }

        return $this;
    }

    public function removeMovieScreeningFormat(MovieScreeningFormat $movieScreeningFormat): static
    {
        if ($this->movieScreeningFormats->removeElement($movieScreeningFormat)) {
            // set the owning side to null (unless already changed)
            if ($movieScreeningFormat->getCinema() === $this) {
                $movieScreeningFormat->setCinema(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Movie>
     */
    public function getMovies(): Collection
    {
        return $this->movies;
    }

    public function addMovie(Movie $movie): static
    {
        if (!$this->movies->contains($movie)) {
            $this->movies->add($movie);
            $movie->setCinema($this);
        }

        return $this;
    }

    public function removeMovie(Movie $movie): static
    {
        if ($this->movies->removeElement($movie)) {
            // set the owning side to null (unless already changed)
            if ($movie->getCinema() === $this) {
                $movie->setCinema(null);
            }
        }

        return $this;
    }


    
}
