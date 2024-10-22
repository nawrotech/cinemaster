<?php

namespace App\Entity;

use App\Repository\ShowtimeRepository;
use Cocur\Slugify\Slugify;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\HasLifecycleCallbacks;
use Doctrine\ORM\Mapping\PrePersist;
use Doctrine\ORM\Mapping\PreUpdate;

#[HasLifecycleCallbacks]
#[ORM\Entity(repositoryClass: ShowtimeRepository::class)]
class Showtime
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'showtimes')]
    #[ORM\JoinColumn(nullable: false)]
    private ?ScreeningRoom $screeningRoom = null;

    #[ORM\Column]
    private ?int $price = null;

    #[ORM\Column]
    private ?int $advertisementTimeInMinutes = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?MovieScreeningFormat $movieScreeningFormat = null;


    #[ORM\ManyToOne(inversedBy: 'showtimes')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Cinema $cinema = null;

    /**
     * @var Collection<int, ReservationSeat>
     */
    #[ORM\OneToMany(targetEntity: ReservationSeat::class, mappedBy: 'showtime')]
    private Collection $reservationSeats;

    #[ORM\Column(length: 255, nullable: false)]
    private ?string $slug = null;

    /**
     * @var Collection<int, Reservation>
     */
    #[ORM\OneToMany(targetEntity: Reservation::class, mappedBy: 'showtime')]
    private Collection $reservations;

    #[ORM\Column]
    private ?bool $isPublished = false;

    #[ORM\Column]
    private ?\DateTimeImmutable $startsAt = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $endsAt = null;

    /**
     * @var Collection<int, VisualFormat>
     */
    #[ORM\ManyToMany(targetEntity: VisualFormat::class)]
    private Collection $supportedVisualFormats;

    public function __construct()
    {
        $this->reservationSeats = new ArrayCollection();
        $this->reservations = new ArrayCollection();
        $this->supportedVisualFormats = new ArrayCollection();
        
    }

    public function displaySlug() {
        return "{$this->movieScreeningFormat->getDisplayMovieScreeningFormat()} - {$this->startsAt->format("Y-m-d H:i:s")}";
    }    

    #[PrePersist]
    public function createSlug(): static
    {
        $slugify = new Slugify();
        $this->slug = $slugify->slugify($this->displaySlug());

        return $this;
    }

    #[PreUpdate]
    public function updateSlug(): static
    {
        $slugify = new Slugify();
        $this->slug = $slugify->slugify($this->displaySlug());

        return $this;
    }

  

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getScreeningRoom(): ?ScreeningRoom
    {
        return $this->screeningRoom;
    }

    public function setScreeningRoom(?ScreeningRoom $screeningRoom): static
    {
        $this->screeningRoom = $screeningRoom;

        return $this;
    }




    public function getPrice(): ?int
    {
        return $this->price;
    }

    public function setPrice(int $price): static
    {
        $this->price = $price;

        return $this;
    }

    public function getAdvertisementTimeInMinutes(): ?int
    {
        return $this->advertisementTimeInMinutes;
    }

    public function setAdvertisementTimeInMinutes(int $advertisementTimeInMinutes): static
    {
        $this->advertisementTimeInMinutes = $advertisementTimeInMinutes;

        return $this;
    }

    public function getMovieScreeningFormat(): ?MovieScreeningFormat
    {
        return $this->movieScreeningFormat;
    }

    public function setMovieScreeningFormat(?MovieScreeningFormat $movieScreeningFormat): static
    {
        $this->movieScreeningFormat = $movieScreeningFormat;

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

    public function getDuration(): int {
        $maintenanceTime = $this->screeningRoom->getMaintenanceTimeInMinutes();
        $movieDurationTime = $this->movieScreeningFormat->getMovie()->getDurationInMinutes();
        $advertisementTime = $this->getAdvertisementTimeInMinutes();
       
        return $advertisementTime + $maintenanceTime + $movieDurationTime;

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
            $reservationSeat->setShowtime($this);
        }

        return $this;
    }

    public function removeReservationSeat(ReservationSeat $reservationSeat): static
    {
        if ($this->reservationSeats->removeElement($reservationSeat)) {
            // set the owning side to null (unless already changed)
            if ($reservationSeat->getShowtime() === $this) {
                $reservationSeat->setShowtime(null);
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

    /**
     * @return Collection<int, Reservation>
     */
    public function getReservations(): Collection
    {
        return $this->reservations;
    }

    public function addReservation(Reservation $reservation): static
    {
        if (!$this->reservations->contains($reservation)) {
            $this->reservations->add($reservation);
            $reservation->setShowtime($this);
        }

        return $this;
    }

    public function removeReservation(Reservation $reservation): static
    {
        if ($this->reservations->removeElement($reservation)) {
            // set the owning side to null (unless already changed)
            if ($reservation->getShowtime() === $this) {
                $reservation->setShowtime(null);
            }
        }

        return $this;
    }

    public function isPublished(): ?bool
    {
        return $this->isPublished;
    }

    public function setPublished(bool $isPublished): static
    {
        $this->isPublished = $isPublished;

        return $this;
    }

    public function getStartsAt(): ?\DateTimeImmutable
    {
        return $this->startsAt;
    }

    public function setStartsAt(\DateTimeImmutable $startsAt): static
    {
        $this->startsAt = $startsAt;

        return $this;
    }

    public function getEndsAt(): ?\DateTimeImmutable
    {
        return $this->endsAt;
    }

    public function setEndsAt(\DateTimeImmutable $endsAt): static
    {
        $this->endsAt = $endsAt;

        return $this;
    }

    /**
     * @return Collection<int, VisualFormat>
     */
    public function getSupportedVisualFormats(): Collection
    {
        return $this->supportedVisualFormats;
    }

    public function addSupportedVisualFormat(VisualFormat $supportedVisualFormat): static
    {
        if (!$this->supportedVisualFormats->contains($supportedVisualFormat)) {
            $this->supportedVisualFormats->add($supportedVisualFormat);
        }

        return $this;
    }

    public function removeSupportedVisualFormat(VisualFormat $supportedVisualFormat): static
    {
        $this->supportedVisualFormats->removeElement($supportedVisualFormat);

        return $this;
    }
}
