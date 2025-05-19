<?php

namespace App\Dto;

class ReservationPriceTierDto
{
    private string $name;
    private float $price;
    private string $color;

    public function __construct(string $name, float $price, string $color)
    {
        $this->name = $name;
        $this->price = $price;
        $this->color = $color;
    }

    public function getName(): string
    {
        return $this->name;
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
        return $this->name . '-' . $this->price . '-' . $this->color;
    }
}