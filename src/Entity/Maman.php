<?php

namespace App\Entity;

use App\Repository\MamanRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: MamanRepository::class)]
#[ORM\HasLifecycleCallbacks]
class Maman
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    /** Téléphone Tunisie : 8 chiffres, premier chiffre 2, 4, 5 ou 9. Préfixe +216 affiché côté front. */
    #[ORM\Column(length: 30)]
    #[Assert\NotBlank(message: 'Le numéro d\'urgence est obligatoire.')]
    #[Assert\Regex(
        pattern: '/^[2459][0-9]{7}$/',
        message: 'Le numéro doit contenir exactement 8 chiffres (Tunisie 🇹🇳) et commencer par 2, 4, 5 ou 9.'
    )]
    private ?string $numeroUrgence = null;

    #[ORM\Column(length: 180, nullable: true)]
    #[Assert\Email(message: 'L\'adresse email "{{ value }}" n\'est pas valide.')]
    private ?string $email = null;

    #[ORM\Column(length: 20)]
    private ?string $groupeSanguin = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $allergies = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $antecedentsMedicaux = null;

    /** Poids en kg. Plage réaliste 30–140 (valeurs humaines). */
    #[ORM\Column]
    #[Assert\NotNull(message: 'Le poids est obligatoire.')]
    #[Assert\Range(min: 30, max: 140, notInRangeMessage: 'Le poids doit être entre {{ min }} et {{ max }} kg.')]
    private ?float $poids = null;

    /** Taille en cm. Plage réaliste 130–220 (OMS). */
    #[ORM\Column]
    #[Assert\NotNull(message: 'La taille est obligatoire.')]
    #[Assert\Range(min: 130, max: 220, notInRangeMessage: 'La taille doit être entre {{ min }} et {{ max }} cm.')]
    private ?float $taille = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $maladiesChroniques = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $medicamentsActuels = null;

    #[ORM\Column]
    private ?bool $fumeur = null;

    #[ORM\Column]
    private ?bool $consommationAlcool = null;

    #[ORM\Column(length: 50)]
    private ?string $niveauActivitePhysique = null;

    #[ORM\Column(length: 100)]
    private ?string $habitudesAlimentaires = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNumeroUrgence(): ?string
    {
        return $this->numeroUrgence;
    }

    public function setNumeroUrgence(string $numeroUrgence): static
    {
        $digits = preg_replace('/\D/', '', $numeroUrgence);
        if (strlen($digits) >= 8) {
            if (str_starts_with($digits, '216') && strlen($digits) >= 11) {
                $digits = substr($digits, 3, 8);
            } else {
                $digits = substr($digits, -8);
            }
        }
        $this->numeroUrgence = $digits !== '' ? $digits : $numeroUrgence;
        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(?string $email): static
    {
        $this->email = $email;
        return $this;
    }

    public function getGroupeSanguin(): ?string
    {
        return $this->groupeSanguin;
    }

    public function setGroupeSanguin(string $groupeSanguin): static
    {
        $this->groupeSanguin = $groupeSanguin;

        return $this;
    }

    public function getAllergies(): ?string
    {
        return $this->allergies;
    }

    public function setAllergies(?string $allergies): static
    {
        $this->allergies = $allergies;

        return $this;
    }

    public function getAntecedentsMedicaux(): ?string
    {
        return $this->antecedentsMedicaux;
    }

    public function setAntecedentsMedicaux(?string $antecedentsMedicaux): static
    {
        $this->antecedentsMedicaux = $antecedentsMedicaux;

        return $this;
    }

    public function getPoids(): ?float
    {
        return $this->poids;
    }

    public function setPoids(?float $poids): static
    {
        $this->poids = $poids;

        return $this;
    }

    public function getTaille(): ?float
    {
        return $this->taille;
    }

    public function setTaille(?float $taille): static
    {
        $this->taille = $taille;

        return $this;
    }

    public function getMaladiesChroniques(): ?string
    {
        return $this->maladiesChroniques;
    }

    public function setMaladiesChroniques(?string $maladiesChroniques): static
    {
        $this->maladiesChroniques = $maladiesChroniques;

        return $this;
    }

    public function getMedicamentsActuels(): ?string
    {
        return $this->medicamentsActuels;
    }

    public function setMedicamentsActuels(?string $medicamentsActuels): static
    {
        $this->medicamentsActuels = $medicamentsActuels;

        return $this;
    }

    public function isFumeur(): ?bool
    {
        return $this->fumeur;
    }

    public function setFumeur(bool $fumeur): static
    {
        $this->fumeur = $fumeur;

        return $this;
    }

    public function isConsommationAlcool(): ?bool
    {
        return $this->consommationAlcool;
    }

    public function setConsommationAlcool(bool $consommationAlcool): static
    {
        $this->consommationAlcool = $consommationAlcool;

        return $this;
    }

    public function getNiveauActivitePhysique(): ?string
    {
        return $this->niveauActivitePhysique;
    }

    public function setNiveauActivitePhysique(string $niveauActivitePhysique): static
    {
        $this->niveauActivitePhysique = $niveauActivitePhysique;

        return $this;
    }

    public function getHabitudesAlimentaires(): ?string
    {
        return $this->habitudesAlimentaires;
    }

    public function setHabitudesAlimentaires(string $habitudesAlimentaires): static
    {
        $this->habitudesAlimentaires = $habitudesAlimentaires;

        return $this;
    }
#[ORM\Column(type: 'datetime')]
private ?\DateTimeInterface $dateCreation = null;

#[ORM\Column(type: 'datetime')]
private ?\DateTimeInterface $dateMiseAJour = null;

/**
 * @var Collection<int, Grosesse>
 */
#[ORM\OneToMany(targetEntity: Grosesse::class, mappedBy: 'maman')]
private Collection $grosesses;

public function __construct()
{
    $this->grosesses = new ArrayCollection();
}

#[ORM\PrePersist]
public function setDateCreationValue(): void
{
    $this->dateCreation = new \DateTime();
    $this->dateMiseAJour = new \DateTime();
}

#[ORM\PreUpdate]
public function setDateMiseAJourValue(): void
{
    $this->dateMiseAJour = new \DateTime();
}

public function getDateCreation(): ?\DateTimeInterface
{
    return $this->dateCreation;
}

public function getDateMiseAJour(): ?\DateTimeInterface
{
    return $this->dateMiseAJour;
}

    /**
     * IMC = poids (kg) / (taille en m)². Null si taille ou poids manquant.
     */
    public function getImc(): ?float
    {
        if ($this->taille === null || $this->taille <= 0 || $this->poids === null || $this->poids <= 0) {
            return null;
        }
        $tailleM = $this->taille / 100;
        return round($this->poids / ($tailleM * $tailleM), 1);
    }

    /**
     * Classification OMS : Maigreur, Normal, Surpoids, Obésité, Obésité sévère.
     */
    public function getImcCategorie(): ?string
    {
        $imc = $this->getImc();
        if ($imc === null) {
            return null;
        }
        if ($imc < 18.5) {
            return 'Maigreur';
        }
        if ($imc < 25) {
            return 'Normal';
        }
        if ($imc < 30) {
            return 'Surpoids';
        }
        if ($imc <= 35) {
            return 'Obésité';
        }
        return 'Obésité sévère';
    }

    /** Alerte admin si IMC < 17 ou IMC > 35. */
    public function isImcAlerte(): bool
    {
        $imc = $this->getImc();
        if ($imc === null) {
            return false;
        }
        return $imc < 17 || $imc > 35;
    }

/**
 * @return Collection<int, Grosesse>
 */
public function getGrosesses(): Collection
{
    return $this->grosesses;
}

public function addGrosess(Grosesse $grosess): static
{
    if (!$this->grosesses->contains($grosess)) {
        $this->grosesses->add($grosess);
        $grosess->setMaman($this);
    }

    return $this;
}

public function removeGrosess(Grosesse $grosess): static
{
    if ($this->grosesses->removeElement($grosess)) {
        if ($grosess->getMaman() === $this) {
            $grosess->setMaman(null);
        }
    }

    return $this;
}
}