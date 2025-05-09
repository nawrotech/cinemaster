<?php

namespace App\Entity;

use App\Repository\VisualFormatRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;


#[ORM\Entity(repositoryClass: VisualFormatRepository::class)]
#[UniqueEntity(
    fields: ['name', 'cinema'],
    message: 'This visual format already exists for this cinema.',
    repositoryMethod: 'findActiveByCinema'
)]
#[ORM\UniqueConstraint(
    name: "unique_active_visual_format",
    columns: ["name", "cinema_id", "active"],
    options: ["where" => "(active = true)"]
)]
class VisualFormat
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 40)]
    #[Assert\NotBlank]
    #[Assert\Length(min: 2, max: 40, minMessage: 'Name must be at least {{ limit }} characters', maxMessage: 'Name cannot be longer than {{ limit }} characters')]
    private ?string $name = null;

    #[ORM\ManyToOne(inversedBy: 'visualFormats')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Cinema $cinema = null;

    #[ORM\Column]
    private ?bool $active = true;

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
        if ($this->id && $name !== $this->name) {
            throw new \RuntimeException('VisualFormat name is immutable.');
        }

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
