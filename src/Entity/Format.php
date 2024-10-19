<?php

namespace App\Entity;

use App\Repository\MovieTypeRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: MovieTypeRepository::class)]
class Format
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $audioVersion = null;

    #[ORM\Column(length: 255)]
    private ?string $visualVersion = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getAudioVersion(): ?string
    {
        return $this->audioVersion;
    }

    public function setAudioVersion(string $audioVersion): static
    {
        $this->audioVersion = $audioVersion;

        return $this;
    }

    public function getVisualVersion(): ?string
    {
        return $this->visualVersion;
    }

    public function setVisualVersion(string $visualVersion): static
    {
        $this->visualVersion = $visualVersion;

        return $this;
    }
}
