<?php

namespace App\Entity;

use App\Contract\SlugInterface;
use App\Repository\CinemaRepository;
use App\Repository\VisualFormatRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

#[UniqueEntity(
    fields: ["name"],
    message: "Name of this cinema is already taken",
    groups: ['cinema']
)]
#[ORM\Entity(repositoryClass: CinemaRepository::class)]
class Cinema implements SlugInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 50, unique: true)]
    #[Assert\NotBlank(message: "Cinema name is required", groups: ['cinema'])]
    #[Assert\Length(
        min: 2,
        max: 50,
        minMessage: "Cinema name must be at least {{ limit }} characters long",
        maxMessage: "Cinema name cannot be longer than {{ limit }} characters",
        groups: ['cinema'])]
    private ?string $name = null;

    #[ORM\Column(length: 100, unique: true)]
    private ?string $slug = null;

    #[ORM\Column]
    #[Assert\NotBlank(message: "Maximum number of rows is required", groups: ['cinema'])]
    #[Assert\Positive(message: "Maximum rows must be a positive number",  groups: ['cinema'])]
    #[Assert\LessThanOrEqual(value: 20, message: "Maximum rows cannot exceed {{ compared_value }}",  groups: ['cinema'])]
    private ?int $maxRows = null;

    #[ORM\Column]
    #[Assert\NotBlank(message: "Maximum seats per row is required", groups: ['cinema'])]
    #[Assert\Positive(message: "Maximum seats per row must be a positive number", groups: ['cinema'])]
    #[Assert\LessThanOrEqual(value: 30, message: "Maximum seats per row cannot exceed {{ compared_value }}",  groups: ['cinema'])]
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
    #[Assert\NotBlank(message: "Street name is required",  groups: ['cinema'])]
    #[Assert\Length(
        min: 3,
        max: 50,
        minMessage: "Street name must be at least {{ limit }} characters long",
        maxMessage: "Street name cannot be longer than {{ limit }} characters",
        groups: ['cinema'])]
    private ?string $streetName = null;

    #[ORM\Column(length: 10)]
    #[Assert\NotBlank(message: "Building number is required",  groups: ['cinema'])]
    #[Assert\Length(
        max: 10,
        maxMessage: "Building number cannot be longer than {{ limit }} characters",
        groups: ['cinema'])]
    private ?string $buildingNumber = null;

    #[ORM\Column(length: 6)]
    #[Assert\NotBlank(message: "Postal code is required", groups: ['cinema'])]
    #[Assert\Length(
        min: 5,
        max: 6,
        minMessage: "Postal code must be at least {{ limit }} characters long",
        maxMessage: "Postal code cannot be longer than {{ limit }} characters",
        groups: ['cinema'])]
    #[Assert\Regex(
        pattern: '/^\d{2}-\d{3}$/',
        message: "Postal code must be in format XX-XXX",
        groups: ['cinema'])]
    private ?string $postalCode = null;

    #[ORM\Column(length: 25)]
    #[Assert\NotBlank(message: "City is required",  groups: ['cinema'])]
    #[Assert\Length(
        min: 2,
        max: 25,
        minMessage: "City name must be at least {{ limit }} characters long",
        maxMessage: "City name cannot be longer than {{ limit }} characters",
        groups: ['cinema'])]
    private ?string $city = null;

    #[ORM\Column(length: 30)]
    #[Assert\NotBlank(message: "District is required",  groups: ['cinema'])]
    #[Assert\Length(
        min: 2,
        max: 30,
        minMessage: "District name must be at least {{ limit }} characters long",
        maxMessage: "District name cannot be longer than {{ limit }} characters",
        groups: ['cinema'])]
    private ?string $district = null;

    #[ORM\Column(length: 50)]
    #[Assert\NotBlank(message: "Country is required",  groups: ['cinema'])]
    #[Assert\Length(
        min: 2,
        max: 50,
        minMessage: "Country name must be at least {{ limit }} characters long",
        maxMessage: "Country name cannot be longer than {{ limit }} characters",
        groups: ['cinema'])]
    #[Assert\Country(message: "This is not a valid country",  groups: ['cinema'])]
    private ?string $country = null;

    #[ORM\ManyToOne(inversedBy: 'cinemas')]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotNull(message: "Cinema owner is required",  groups: ['cinema'])]
    private ?User $owner = null;

    /**
     * @var Collection<int, VisualFormat>
     */
    #[ORM\OneToMany(targetEntity: VisualFormat::class, mappedBy: 'cinema', cascade: ["persist"])]
    #[Assert\Valid(groups: ['visual_formats'])]
    private Collection $visualFormats;

    /**
     * @var Collection<int, ScreeningRoomSetup>
     */
    #[ORM\OneToMany(targetEntity: ScreeningRoomSetup::class, mappedBy: 'cinema', cascade: ["persist"])]
    #[Assert\Valid()]
    private Collection $screeningRoomSetups;

    /**
     * @var Collection<int, ScreeningFormat>
     */
    #[ORM\OneToMany(targetEntity: ScreeningFormat::class, mappedBy: 'cinema', cascade: ["persist"])]
    #[Assert\Valid()]
    private Collection $screeningFormats;

    /**
     * @var Collection<int, MovieScreeningFormat>
     */
    #[ORM\OneToMany(targetEntity: MovieScreeningFormat::class, mappedBy: 'cinema')]
    #[Assert\Valid()]
    private Collection $movieScreeningFormats;

    /**
     * @var Collection<int, Movie>
     */
    #[ORM\OneToMany(targetEntity: Movie::class, mappedBy: 'cinema')]
    private Collection $movies;

    #[ORM\Column(type: Types::TIME_IMMUTABLE)]
    #[Assert\NotNull(message: "Opening time is required")]
    private ?\DateTimeImmutable $openTime = null;

    #[ORM\Column(type: Types::TIME_IMMUTABLE)]
    #[Assert\NotNull(message: "Closing time is required", groups: ['cinema'])]
    #[Assert\Expression(
        "this.getOpenTime() != this.getCloseTime()",
        message: "Closing time must be different than opening time",
        groups: ['cinema']
    )]
    private ?\DateTimeImmutable $closeTime = null;

    public function __toString()
    {
        return $this->name;
    }

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
     * @phpstan-return \Doctrine\Common\Collections\Collection<int, VisualFormat>
     */
    public function getVisualFormats(): Collection
    {
        return $this->visualFormats
            ->matching(VisualFormatRepository::activeVisualFormatsConstraint());
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

    public function getOpenTime(): ?\DateTimeImmutable
    {
        return $this->openTime;
    }

    public function setOpenTime(\DateTimeImmutable $openTime): static
    {
        $this->openTime = $openTime;

        return $this;
    }

    public function getCloseTime(): ?\DateTimeImmutable
    {
        return $this->closeTime;
    }

    public function setCloseTime(\DateTimeImmutable $closeTime): static
    {
        $this->closeTime = $closeTime;

        return $this;
    }
}
