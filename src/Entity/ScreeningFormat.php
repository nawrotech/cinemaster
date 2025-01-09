<?php

namespace App\Entity;

use App\Enum\LanguagePresentation;
use App\Repository\ScreeningFormatRepository;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\Collection;

#[ORM\Entity(repositoryClass: ScreeningFormatRepository::class)]
class ScreeningFormat
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: "screeningFormats")]
    #[ORM\JoinColumn(nullable: false)]
    private ?VisualFormat $visualFormat = null;

    #[ORM\Column(type: "string", enumType: LanguagePresentation::class)]
    private ?LanguagePresentation $languagePresentation = null;

    #[ORM\ManyToOne(inversedBy: 'screeningFormats')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Cinema $cinema = null;

    #[ORM\OneToMany(mappedBy: "screeningFormat", targetEntity: MovieScreeningFormat::class, orphanRemoval: true)]
    private Collection $movieScreeningFormats;

    #[ORM\Column]
    private ?bool $active = true;


    public function getId(): ?int
    {
        return $this->id;
    }

    public function getVisualFormat(): ?VisualFormat
    {
        return $this->visualFormat;
    }

    public function setVisualFormat(?VisualFormat $visualFormat): static
    {
        if ($this->visualFormat !== null) {
            throw new \LogicException('VisualFormat cannot be changed once set.');
        }
        $this->visualFormat = $visualFormat;

        return $this;
    }

    public function getLanguagePresentation(): ?LanguagePresentation
    {
        return $this->languagePresentation;
    }

    public function setLanguagePresentation(LanguagePresentation $languagePresentation): static
    {
        if ($this->languagePresentation !== null) {
            throw new \LogicException('LanguagePresentation cannot be changed once set.');
        }

        $this->languagePresentation = $languagePresentation;

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

    public function getDisplayScreeningFormat() {

        return "{$this->getVisualFormat()->getName()} {$this->getLanguagePresentation()->value}";
    }

    public function isActive(): ?bool
    {
        return $this->active;
    }

    public function setActive(bool $active): static
    {
        $this->active = $active;

        return $this;
    }

}
