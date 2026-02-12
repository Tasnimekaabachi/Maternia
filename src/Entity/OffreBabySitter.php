<?php

namespace App\Entity;

use App\Repository\OffreBabySitterRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: OffreBabySitterRepository::class)]
class OffreBabySitter
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: 'Le nom de la babysitter est obligatoire.')]
    #[Assert\Length(
        min: 3,
        max: 255,
        minMessage: 'Le nom doit contenir au minimum {{ limit }} caractères.',
        maxMessage: 'Le nom doit contenir au maximum {{ limit }} caractères.'
    )]
    private ?string $nomBabysitter = null;

    #[ORM\Column(length: 20)]
    #[Assert\NotBlank(message: 'Le numéro de téléphone est obligatoire.')]
    #[Assert\Regex(
        pattern: '/^\+?[0-9\s]{8,20}$/',
        message: 'Le numéro de téléphone n\'est pas valide.'
    )]
    private ?string $telephone = null;

    #[ORM\Column]
    #[Assert\NotNull(message: 'L\'expérience est obligatoire.')]
    #[Assert\PositiveOrZero(message: 'L\'expérience doit être un nombre positif.')]
    private ?int $experience = null;

    #[ORM\Column(length: 100)]
    #[Assert\NotBlank(message: 'La ville est obligatoire.')]
    #[Assert\Length(
        min: 2,
        max: 100,
        minMessage: 'La ville doit contenir au minimum {{ limit }} caractères.',
        maxMessage: 'La ville doit contenir au maximum {{ limit }} caractères.'
    )]
    private ?string $ville = null;

    #[ORM\Column]
    #[Assert\NotNull(message: 'Le tarif est obligatoire.')]
    #[Assert\Positive(message: 'Le tarif doit être un nombre positif.')]
    private ?float $tarif = null;

    #[ORM\Column(type: Types::TEXT)]
    #[Assert\NotBlank(message: 'La description est obligatoire.')]
    #[Assert\Length(
        min: 10,
        minMessage: 'La description doit contenir au minimum {{ limit }} caractères.'
    )]
    private ?string $description = null;

    #[ORM\Column]
    #[Assert\NotNull(message: 'La disponibilité est obligatoire.')]
    private ?bool $disponibilite = null;

    // 🔗 Relation avec DemandeBabySitter
    #[ORM\OneToMany(mappedBy: 'offre', targetEntity: DemandeBabySitter::class, cascade: ['persist', 'remove'])]
    private Collection $demandes;

    public function __construct()
    {
        $this->demandes = new ArrayCollection();
    }

    // ---------------- GETTERS & SETTERS ----------------

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNomBabysitter(): ?string
    {
        return $this->nomBabysitter;
    }

    public function setNomBabysitter(string $nomBabysitter): self
    {
        $this->nomBabysitter = $nomBabysitter;
        return $this;
    }

    public function getTelephone(): ?string
    {
        return $this->telephone;
    }

    public function setTelephone(string $telephone): self
    {
        $this->telephone = $telephone;
        return $this;
    }

    public function getExperience(): ?int
    {
        return $this->experience;
    }

    public function setExperience(int $experience): self
    {
        $this->experience = $experience;
        return $this;
    }

    public function getVille(): ?string
    {
        return $this->ville;
    }

    public function setVille(string $ville): self
    {
        $this->ville = $ville;
        return $this;
    }

    public function getTarif(): ?float
    {
        return $this->tarif;
    }

    public function setTarif(float $tarif): self
    {
        $this->tarif = $tarif;
        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): self
    {
        $this->description = $description;
        return $this;
    }

    public function isDisponibilite(): ?bool
    {
        return $this->disponibilite;
    }

    public function setDisponibilite(bool $disponibilite): self
    {
        $this->disponibilite = $disponibilite;
        return $this;
    }

    /**
     * @return Collection<int, DemandeBabySitter>
     */
    public function getDemandes(): Collection
    {
        return $this->demandes;
    }

    public function addDemande(DemandeBabySitter $demande): self
    {
        if (!$this->demandes->contains($demande)) {
            $this->demandes->add($demande);
            $demande->setOffre($this);
        }
        return $this;
    }

    public function removeDemande(DemandeBabySitter $demande): self
    {
        if ($this->demandes->removeElement($demande)) {
            if ($demande->getOffre() === $this) {
                $demande->setOffre(null);
            }
        }
        return $this;
    }
}
