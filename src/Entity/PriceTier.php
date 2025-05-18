<?php

namespace App\Entity;

use App\Repository\PriceTierRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: PriceTierRepository::class)]
#[UniqueEntity(
    fields: ['name', 'price', 'cinema'],
    message: 'This price tier already exists for this cinema.',
    repositoryMethod: 'findActiveByCinema'
)]
#[ORM\UniqueConstraint(
    name: "unique_active_price_tier",
    columns: ["name", "price", "cinema_id", "is_active"],
    options: ["where" => "(is_active = true)"]
)]
class PriceTier
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 30)]
    #[Assert\NotBlank]
    #[Assert\Length(max: 30)]
    private ?string $name = null;

    #[ORM\Column]
    #[Assert\NotBlank]
    #[Assert\Positive]
    private ?float $price = null;

    #[ORM\Column]
    private ?bool $isActive = true;

    #[ORM\ManyToOne(inversedBy: 'priceTiers')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Cinema $cinema = null;


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

    public function getPrice(): ?float
    {
        return $this->price;
    }

    public function setPrice(float $price): static
    {
        $this->price = $price;

        return $this;
    }

    public function isActive(): ?bool
    {
        return $this->isActive;
    }

    public function setIsActive(bool $isActive): static
    {
        $this->isActive = $isActive;

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
