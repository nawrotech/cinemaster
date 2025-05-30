<?php

namespace App\Entity;

use App\Enum\SeatPricing;
use App\Repository\PriceTierRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: PriceTierRepository::class)]
#[UniqueEntity(
    fields: ['type', 'price', 'cinema'],
    message: 'This price tier already exists for this cinema.',
    repositoryMethod: 'findActiveByCinema'
)]
#[ORM\UniqueConstraint(
    name: "unique_active_price_tier",
    columns: ["type", "price", "cinema_id", "is_active"],
    options: ["where" => "(is_active = true)"]
)]
class PriceTier
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;


    #[ORM\Column]
    #[Assert\NotBlank]
    #[Assert\Positive]
    private ?float $price = null;

    #[ORM\Column]
    private ?bool $isActive = true;

    #[ORM\ManyToOne(inversedBy: 'priceTiers')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Cinema $cinema = null;

    #[ORM\Column(length: 7)]
    #[Assert\NotNull]
    #[Assert\CssColor()]
    private ?string $color = null;

    #[ORM\Column(type: 'string', enumType: SeatPricing::class)]
    private ?SeatPricing $type = null;


    public function getId(): ?int
    {
        return $this->id;
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

    public function getColor(): ?string
    {
        return $this->color;
    }

    public function setColor(string $color): static
    {
        $this->color = $color;

        return $this;
    }

    public function getType(): ?SeatPricing
    {
        return $this->type;
    }

    public function setType(SeatPricing $type): static
    {
        $this->type = $type;

        return $this;
    }

}
