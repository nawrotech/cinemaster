<?php

namespace App\Entity;

use App\Repository\ScreeningSetupTypeRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ScreeningSetupTypeRepository::class)]
class ScreeningSetupType
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 50)]
    private ?string $soundFormat = null;

    #[ORM\ManyToOne(inversedBy: 'screeningSetupTypes')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Cinema $cinema = null;

    #[ORM\ManyToOne(inversedBy: 'screeningSetupTypes')]
    #[ORM\JoinColumn(nullable: false)]
    private ?VisualFormat $visualFormat = null;

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
}
