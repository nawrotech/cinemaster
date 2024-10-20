<?php

namespace App\Entity;

use App\Repository\FormatRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: FormatRepository::class)]
class Format
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?VisualFormat $visualFormat = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?AudioFormat $audioFormat = null;



    public function getId(): ?int
    {
        return $this->id;
    }

    public function getVisualFormat(): ?VisualFormat
    {
        return $this->visualFormat;
    }

    public function setVisualFormat(?VisualFormat $visualFormat): static
    {
        $this->visualFormat = $visualFormat;

        return $this;
    }

    public function getAudioFormat(): ?AudioFormat
    {
        return $this->audioFormat;
    }

    public function setAudioFormat(?AudioFormat $audioFormat): static
    {
        $this->audioFormat = $audioFormat;

        return $this;
    }

}
