<?php

namespace App\Entity;

use App\Enum\ScreeningRoomSeatType;
use App\Enum\SeatPricing;
use App\Repository\ScreeningRoomSeatRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ScreeningRoomSeatRepository::class)]
class ScreeningRoomSeat
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'screeningRoomSeats')]
    #[ORM\JoinColumn(nullable: false)]
    private ?ScreeningRoom $screeningRoom = null;

    #[ORM\Column(length: 15)]
    private ?string $status = "available";

    #[ORM\Column(type: "string", enumType: ScreeningRoomSeatType::class)]
    private ?ScreeningRoomSeatType $type = ScreeningRoomSeatType::REGULAR;

    #[ORM\Column]
    private ?bool $isVisible = true;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?Seat $seat = null;

    #[ORM\Column(enumType: SeatPricing::class)]
    private ?SeatPricing $pricingType = SeatPricing::PREMIUM;

    #[ORM\ManyToOne()]
    #[ORM\JoinColumn(nullable: true)]
    private ?PriceTier $priceTier = null;


    public function getId(): ?int
    {
        return $this->id;
    }

    public function getScreeningRoom(): ?ScreeningRoom
    {
        return $this->screeningRoom;
    }

    public function setScreeningRoom(?ScreeningRoom $screeningRoom): static
    {
        $this->screeningRoom = $screeningRoom;

        return $this;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(string $status): static
    {
        $this->status = $status;

        return $this;
    }

    public function getType(): ?ScreeningRoomSeatType
    {
        return $this->type;
    }

    public function setType(ScreeningRoomSeatType $type): static
    {
        $this->type = $type;

        return $this;
    }

    public function isVisible(): ?bool
    {
        return $this->isVisible;
    }

    public function setVisible(bool $isVisible): static
    {
        $this->isVisible = $isVisible;

        return $this;
    }

    public function getSeat(): ?Seat
    {
        return $this->seat;
    }

    public function setSeat(?Seat $seat): static
    {
        $this->seat = $seat;

        return $this;
    }

    public function getPricingType(): ?SeatPricing
    {
        return $this->pricingType;
    }

    public function setPricingType(SeatPricing $pricingType): static
    {
        $this->pricingType = $pricingType;

        return $this;
    }

    public function getPriceTier(): ?PriceTier
    {
        return $this->priceTier;
    }

    public function setPriceTier(?PriceTier $priceTier): static
    {
        $this->priceTier = $priceTier;

        return $this;
    }


}
