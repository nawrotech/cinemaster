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

    #[ORM\Column(length: 10)]
    private ?string $name = null;

    #[ORM\ManyToOne(inversedBy: 'visualFormats')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Cinema $cinema = null;

    /**
     * @var Collection<int, ScreeningSetupType>
     */
    #[ORM\OneToMany(targetEntity: ScreeningSetupType::class, mappedBy: 'visualFormat', orphanRemoval: true)]
    private Collection $screeningSetupTypes;

    public function __construct()
    {
        $this->screeningSetupTypes = new ArrayCollection();
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
     * @return Collection<int, ScreeningSetupType>
     */
    public function getScreeningSetupTypes(): Collection
    {
        return $this->screeningSetupTypes;
    }

    public function addScreeningSetupType(ScreeningSetupType $screeningSetupType): static
    {
        if (!$this->screeningSetupTypes->contains($screeningSetupType)) {
            $this->screeningSetupTypes->add($screeningSetupType);
            $screeningSetupType->setVisualFormat($this);
        }

        return $this;
    }

    public function removeScreeningSetupType(ScreeningSetupType $screeningSetupType): static
    {
        if ($this->screeningSetupTypes->removeElement($screeningSetupType)) {
            // set the owning side to null (unless already changed)
            if ($screeningSetupType->getVisualFormat() === $this) {
                $screeningSetupType->setVisualFormat(null);
            }
        }

        return $this;
    }
}
