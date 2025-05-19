<?php

namespace App\Dto;

use App\Enum\SeatPricing;

class ReservationPriceTierDto
{
    private SeatPricing $type;
    private float $price;
    private string $color;

    public function __construct(SeatPricing $type, float $price, string $color)
    {
        $this->type = $type;
        $this->price = $price;
        $this->color = $color;
    }

    public function getType(): SeatPricing
    {
        return $this->type;
    }

    public function getPrice(): float
    {
        return $this->price;
    }

    public function getColor(): string
    {
        return $this->color;
    }

    public function getKey(): string
    {
        return $this->type . '-' . $this->price . '-' . $this->color;
    }
}