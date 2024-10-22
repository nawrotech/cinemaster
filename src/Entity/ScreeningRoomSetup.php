<?php

namespace App\Entity;

use App\Repository\ScreeningRoomSetupRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ScreeningRoomSetupRepository::class)]
class ScreeningRoomSetup
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 50)]
    private ?string $soundFormat = null;

    #[ORM\ManyToOne(inversedBy: 'screeningRoomSetups')]
    #[ORM\JoinColumn(nullable: false)]
    private ?VisualFormat $visualFormat = null;

    #[ORM\ManyToOne(inversedBy: 'screeningRoomSetups')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Cinema $cinema = null;

    /**
     * @var Collection<int, ScreeningRoom>
     */
    #[ORM\OneToMany(targetEntity: ScreeningRoom::class, mappedBy: 'screeningRoomSetup')]
    private Collection $screeningRooms;

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
        $this->visualFormat = $visualFormat;

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
            $screeningRoom->setScreeningRoomSetup($this);
        }

        return $this;
    }

    public function removeScreeningRoom(ScreeningRoom $screeningRoom): static
    {
        if ($this->screeningRooms->removeElement($screeningRoom)) {
            // set the owning side to null (unless already changed)
            if ($screeningRoom->getScreeningRoomSetup() === $this) {
                $screeningRoom->setScreeningRoomSetup(null);
            }
        }

        return $this;
    }

    public function getDisplaySetup() {
        return "Sound: {$this->soundFormat} Vision: {$this->visualFormat->getName()}";
    }
}
