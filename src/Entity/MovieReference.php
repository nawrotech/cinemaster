<?php

namespace App\Entity;

use App\Repository\MovieReferenceRepository;
use App\Service\UploaderHelper;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;

#[ORM\Entity(repositoryClass: MovieReferenceRepository::class)]
class MovieReference
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups("main")]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'movieReferences')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Movie $movie = null;

    #[ORM\Column(length: 255)]
    #[Groups("main")]
    private ?string $filename = null;

    #[ORM\Column(length: 255)]
    #[Groups(["main", "input"])]
    private ?string $originalFilename = null;

    #[ORM\Column(length: 255)]
    #[Groups("main")]
    private ?string $mimeType = null;

    #[ORM\Column(nullable: true)]
    private ?int $position = 0;

    public function __construct(Movie $movie)
    {
        $this->movie = $movie;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getMovie(): ?Movie
    {
        return $this->movie;
    }


    public function getFilename(): ?string
    {
        return $this->filename;
    }

    public function setFilename(string $filename): static
    {
        $this->filename = $filename;

        return $this;
    }

    public function getOriginalFilename(): ?string
    {
        return $this->originalFilename;
    }

    public function setOriginalFilename(string $originalFilename): static
    {
        $this->originalFilename = $originalFilename;

        return $this;
    }

    public function getMimeType(): ?string
    {
        return $this->mimeType;
    }

    public function setMimeType(string $mimeType): static
    {
        $this->mimeType = $mimeType;

        return $this;
    }


    public function getFilePath(): string {
        return UploaderHelper::MOVIE_REFERENCE . "/{$this->filename}";
    }

    public function getPosition(): ?int
    {
        return $this->position;
    }

    public function setPosition(?int $position): static
    {
        $this->position = $position;

        return $this;
    }

}
