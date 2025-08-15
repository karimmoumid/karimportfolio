<?php

namespace App\Entity;

use App\Repository\ProjectRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ProjectRepository::class)]
class Project
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $title = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $description = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $full_description = null;

    #[ORM\Column(length: 150, nullable: true)]
    private ?string $image = null;

    #[ORM\Column(nullable: true)]
    private ?int $view_count = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $created_at = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $updated_at = null;

    /**
     * @var Collection<int, ProjectImage>
     */
    #[ORM\OneToMany(targetEntity: ProjectImage::class, mappedBy: 'project')]
    private Collection $images;

    /**
     * @var Collection<int, Skill>
     */
    #[ORM\ManyToMany(targetEntity: Skill::class, inversedBy: 'projects')]
    private Collection $skill;

    /**
     * @var Collection<int, Testimonial>
     */
    #[ORM\OneToMany(targetEntity: Testimonial::class, mappedBy: 'project')]
    private Collection $testimonial;

    public function __construct()
    {
        $this->images = new ArrayCollection();
        $this->skill = new ArrayCollection();
        $this->testimonial = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): static
    {
        $this->title = $title;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function getFullDescription(): ?string
    {
        return $this->full_description;
    }

    public function setFullDescription(string $full_description): static
    {
        $this->full_description = $full_description;

        return $this;
    }

    public function getImage(): ?string
    {
        return $this->image;
    }

    public function setImage(?string $image): static
    {
        $this->image = $image;

        return $this;
    }

    public function getViewCount(): ?int
    {
        return $this->view_count;
    }

    public function setViewCount(?int $view_count): static
    {
        $this->view_count = $view_count;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->created_at;
    }

    public function setCreatedAt(\DateTimeImmutable $created_at): static
    {
        $this->created_at = $created_at;

        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updated_at;
    }

    public function setUpdatedAt(?\DateTimeImmutable $updated_at): static
    {
        $this->updated_at = $updated_at;

        return $this;
    }

    /**
     * @return Collection<int, ProjectImage>
     */
    public function getImages(): Collection
    {
        return $this->images;
    }

    public function addImage(ProjectImage $image): static
    {
        if (!$this->images->contains($image)) {
            $this->images->add($image);
            $image->setProject($this);
        }

        return $this;
    }

    public function removeImage(ProjectImage $image): static
    {
        if ($this->images->removeElement($image)) {
            // set the owning side to null (unless already changed)
            if ($image->getProject() === $this) {
                $image->setProject(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Skill>
     */
    public function getSkill(): Collection
    {
        return $this->skill;
    }

    public function addSkill(Skill $skill): static
    {
        if (!$this->skill->contains($skill)) {
            $this->skill->add($skill);
        }

        return $this;
    }

    public function removeSkill(Skill $skill): static
    {
        $this->skill->removeElement($skill);

        return $this;
    }

    /**
     * @return Collection<int, Testimonial>
     */
    public function getTestimonial(): Collection
    {
        return $this->testimonial;
    }

    public function addTestimonial(Testimonial $testimonial): static
    {
        if (!$this->testimonial->contains($testimonial)) {
            $this->testimonial->add($testimonial);
            $testimonial->setProject($this);
        }

        return $this;
    }

    public function removeTestimonial(Testimonial $testimonial): static
    {
        if ($this->testimonial->removeElement($testimonial)) {
            // set the owning side to null (unless already changed)
            if ($testimonial->getProject() === $this) {
                $testimonial->setProject(null);
            }
        }

        return $this;
    }
}
