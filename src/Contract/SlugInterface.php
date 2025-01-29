<?php

namespace App\Contract;

interface SlugInterface {

    public function __toString();

    public function getSlug(): ?string;

    public function setSlug(string $slug): static;
}

