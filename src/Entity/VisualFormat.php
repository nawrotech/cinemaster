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


}
