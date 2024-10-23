<?php

namespace App\Entity;

use App\Repository\VisualFormatRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: VisualFormatRepository::class)]
class VisualFormat
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 40)]
    private ?string $name = null;

    #[ORM\ManyToOne(inversedBy: 'visualFormats')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Cinema $cinema = null;

    /**
     * @var Collection<int, ScreeningRoomSetup>
     */
    #[ORM\OneToMany(targetEntity: ScreeningRoomSetup::class, mappedBy: 'visualFormat', orphanRemoval: true)]
    private Collection $screeningRoomSetups;

   /**
     * @var Collection<int, ScreeningFormat>
     */
    #[ORM\OneToMany(targetEntity: ScreeningFormat::class, mappedBy: 'visualFormat', orphanRemoval: true)]
    private Collection $screeningFormats;


    public function __construct()
    {
        $this->screeningRoomSetups = new ArrayCollection();
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

    public function getCinema(): ?Cinema
    {
        return $this->cinema;
    }

    public function setCinema(?Cinema $cinema): static
    {
        $this->cinema = $cinema;

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
            $screeningRoomSetup->setVisualFormat($this);
        }

        return $this;
    }

    public function removeScreeningRoomSetup(ScreeningRoomSetup $screeningRoomSetup): static
    {
        if ($this->screeningRoomSetups->removeElement($screeningRoomSetup)) {
            // set the owning side to null (unless already changed)
            if ($screeningRoomSetup->getVisualFormat() === $this) {
                $screeningRoomSetup->setVisualFormat(null);
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
            $screeningFormat->setVisualFormat($this);
        }

        return $this;
    }

    public function removeScreeningFormat(ScreeningFormat $screeningFormat): static
    {

        if ($this->screeningFormats->removeElement($screeningFormat)) {
            // set the owning side to null (unless already changed)
            if ($screeningFormat->getVisualFormat() === $this) {
                $screeningFormat->setVisualFormat(null);
            }
        }

        return $this;
    }
}
