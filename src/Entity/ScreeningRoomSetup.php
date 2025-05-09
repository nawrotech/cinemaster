<?php

namespace App\Entity;

use App\Repository\ScreeningRoomSetupRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ScreeningRoomSetupRepository::class)]
#[ORM\UniqueConstraint(
    name: "unique_active_screening_room_setup",
    columns: ["sound_format", "visual_format_id", "cinema_id", "is_active"],
    options: ["where" => "(is_active = true)"]
)]
#[UniqueEntity(
    fields: ['soundFormat', 'visualFormat', 'cinema'],
    message: 'This screening room setup already exists for this cinema.',
    repositoryMethod: 'findActiveByCinema'
)]
class ScreeningRoomSetup
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 50)]
    #[Assert\NotBlank()]
    #[Assert\Length(min: 2, max: 50)]
    private ?string $soundFormat = null;

    #[ORM\ManyToOne(inversedBy: 'screeningRoomSetups')]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotNull()]
    private ?VisualFormat $visualFormat = null;

    #[ORM\ManyToOne(inversedBy: 'screeningRoomSetups')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Cinema $cinema = null;

    /**
     * @var Collection<int, ScreeningRoom>
     */
    #[ORM\OneToMany(targetEntity: ScreeningRoom::class, mappedBy: 'screeningRoomSetup')]
    private Collection $screeningRooms;

    #[ORM\Column]
    private ?bool $isActive = true;

    public function __construct()
    {
        $this->screeningRooms = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }


    public function getSoundFormat(): ?string
    {
        return $this->soundFormat;
    }

    public function setSoundFormat(string $soundFormat): static
    {
        
        if ($this->id && $soundFormat !== $this->soundFormat) {
            throw new \RuntimeException('SoundFormat name is immutable.');
        }
        $this->soundFormat = $soundFormat;

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

    public function getVisualFormat(): ?VisualFormat
    {
        return $this->visualFormat;
    }

    public function setVisualFormat(?VisualFormat $visualFormat): static
    {
        if ($this->id && $visualFormat !== $this->visualFormat) {
            throw new \RuntimeException('VisualFormat name is immutable.');
        }

        $this->visualFormat = $visualFormat;
        return $this;
    }


    public function getDisplaySetup() {
        return "Sound: {$this->soundFormat}, Vision: {$this->visualFormat->getName()}";
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
            $screeningRoom->setScreeningRoomSetup($this);
        }

        return $this;
    }

    public function removeScreeningRoom(ScreeningRoom $screeningRoom): static
    {
        if ($this->screeningRooms->removeElement($screeningRoom)) {
            if ($screeningRoom->getScreeningRoomSetup() === $this) {
                $screeningRoom->setScreeningRoomSetup(null);
            }
        }

        return $this;
    }

    public function isActive(): ?bool
    {
        return $this->isActive;
    }

    public function setActive(bool $isActive): static
    {
        $this->isActive = $isActive;

        return $this;
    }
}
