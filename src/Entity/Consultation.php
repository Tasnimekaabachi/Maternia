<?php

namespace App\Entity;

use App\Repository\ConsultationRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ConsultationRepository::class)]
class Consultation
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 100)]
    #[Assert\NotBlank(message: "La catégorie est obligatoire.")]
    private ?string $categorie = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $description = null;

    #[ORM\Column(length: 50)]
    #[Assert\NotBlank(message: "Le public cible (Pour) est obligatoire.")]
    private ?string $pour = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $image = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $icon = null;

    #[ORM\Column]
    private ?bool $statut = null;

    #[ORM\Column(nullable: true)]
    private ?int $ordreAffichage = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTime $createdAt = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTime $updatedAt = null;

    /**
     * @var Collection<int, ConsultationCreneau>
     */
    #[ORM\OneToMany(targetEntity: ConsultationCreneau::class, mappedBy: 'consultation')]
    private Collection $consultationCreneaus;

    public function __construct()
    {
        $this->consultationCreneaus = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCategorie(): ?string
    {
        return $this->categorie;
    }

    public function setCategorie(string $categorie): static
    {
        $this->categorie = $categorie;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function getPour(): ?string
    {
        return $this->pour;
    }

    public function setPour(string $pour): static
    {
        $this->pour = $pour;

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

    public function getIcon(): ?string
    {
        return $this->icon;
    }

    public function setIcon(?string $icon): static
    {
        $this->icon = $icon;

        return $this;
    }

    public function isStatut(): ?bool
    {
        return $this->statut;
    }

    public function setStatut(bool $statut): static
    {
        $this->statut = $statut;

        return $this;
    }

    public function getOrdreAffichage(): ?int
    {
        return $this->ordreAffichage;
    }

    public function setOrdreAffichage(?int $ordreAffichage): static
    {
        $this->ordreAffichage = $ordreAffichage;

        return $this;
    }

    public function getCreatedAt(): ?\DateTime
    {
        return $this->createdAt;
    }

    public function setCreatedAt(?\DateTime $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getUpdatedAt(): ?\DateTime
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(?\DateTime $updatedAt): static
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    /**
     * @return Collection<int, ConsultationCreneau>
     */
    public function getConsultationCreneaus(): Collection
    {
        return $this->consultationCreneaus;
    }

    public function addConsultationCreneau(ConsultationCreneau $consultationCreneau): static
    {
        if (!$this->consultationCreneaus->contains($consultationCreneau)) {
            $this->consultationCreneaus->add($consultationCreneau);
            $consultationCreneau->setConsultation($this);
        }

        return $this;
    }

    public function removeConsultationCreneau(ConsultationCreneau $consultationCreneau): static
    {
        if ($this->consultationCreneaus->removeElement($consultationCreneau)) {
            // set the owning side to null (unless already changed)
            if ($consultationCreneau->getConsultation() === $this) {
                $consultationCreneau->setConsultation(null);
            }
        }

        return $this;
    }
}
