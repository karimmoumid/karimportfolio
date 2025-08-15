<?php

namespace App\Entity;

use App\Repository\ProjectImageRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ProjectImageRepository::class)]
class ProjectImage
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 150)]
    private ?string $filename = null;

    #[ORM\Column(length: 255)]
    private ?string $original_name = null;

    #[ORM\Column(length: 255)]
    private ?string $alt_text = null;

    #[ORM\ManyToOne(inversedBy: 'images')]
    private ?Project $project = null;

    public function getId(): ?int
    {
        return $this->id;
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

    public function getOriginalName(): ?string
    {
        return $this->original_name;
    }

    public function setOriginalName(string $original_name): static
    {
        $this->original_name = $original_name;

        return $this;
    }

    public function getAltText(): ?string
    {
        return $this->alt_text;
    }

    public function setAltText(string $alt_text): static
    {
        $this->alt_text = $alt_text;

        return $this;
    }

    public function getProject(): ?Project
    {
        return $this->project;
    }

    public function setProject(?Project $project): static
    {
        $this->project = $project;

        return $this;
    }
}
