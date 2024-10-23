<?php

namespace App\Entity;

use App\Enum\LanguagePresentation;
use App\Repository\ScreeningFormatRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ScreeningFormatRepository::class)]
class ScreeningFormat
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?VisualFormat $visualFormat = null;

    #[ORM\Column()]
    private ?string $languagePresentation = null;

    #[ORM\ManyToOne(inversedBy: 'screeningFormats')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Cinema $cinema = null;


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
        $this->visualFormat = $visualFormat;

        return $this;
    }

    public function getLanguagePresentation(): ?string
    {
        return $this->languagePresentation;
    }

    public function setLanguagePresentation(string $languagePresentation): static
    {

        if (!in_array($languagePresentation, LanguagePresentation::getValuesArray())) {
            throw new \InvalidArgumentException("Invalid status");
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


}
