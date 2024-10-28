<?php

namespace App\Contracts;

interface MovieInterface {
    public function getId(): ?int;
    public function getPosterPath(): ?string;
    public function getTitle(): ?string;
    public function getOverview(): ?string;
    public function getReleaseDate(): ?\DateTimeImmutable;
    public function getDurationInMinutes(): ?int;

}