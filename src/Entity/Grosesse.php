<?php

namespace App\Entity;

use App\Repository\GrosesseRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

#[ORM\Entity(repositoryClass: GrosesseRepository::class)]
#[ORM\HasLifecycleCallbacks]
class Grosesse
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(options: ['default' => false])]
    private bool $connaitDDR = false;

    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true)]
    #[Assert\LessThanOrEqual('today', message: 'La date doit être antérieure ou égale à aujourd’hui.')]
    #[Assert\GreaterThan('-10 months', message: 'La date ne peut pas être antérieure à 10 mois.')]
    private ?\DateTime $dateDernieresRegles = null;

    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true)]
    #[Assert\LessThanOrEqual('today', message: 'La date doit être antérieure ou égale à aujourd’hui.')]
    #[Assert\GreaterThan('-10 months', message: 'La date ne peut pas être antérieure à 10 mois.')]
    private ?\DateTime $dateDebutGrossesse = null;

    #[ORM\Column(length: 50)]
    #[Assert\Choice(choices: ['enCours', 'aRisque', 'terminee'], message: 'Choisissez un statut valide.')]
    private ?string $statutGrossesse = null;

    #[ORM\Column(length: 50)]
    #[Assert\Choice(choices: ['simple', 'multiple'], message: 'Choisissez un type valide.')]
    private ?string $typeGrossesse = null;

    #[ORM\Column(nullable: true)]
    #[Assert\Positive(message: 'Le poids doit être positif.')]
    #[Assert\Range(min: 30, max: 200, notInRangeMessage: 'Le poids doit être entre {{ min }} et {{ max }} kg.')]
    private ?float $poidsActuel = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    #[Assert\Length(max: 2000, maxMessage: 'Les symptômes ne peuvent pas dépasser {{ limit }} caractères.')]
    private ?string $symptomes = null;

    #[ORM\Column(options: ['default' => false])]
    private bool $nausee = false;

    #[ORM\Column(options: ['default' => false])]
    private bool $vomissement = false;

    #[ORM\Column(options: ['default' => false])]
    private bool $saignement = false;

    #[ORM\Column(options: ['default' => false])]
    private bool $fievre = false;

    #[ORM\Column(options: ['default' => false])]
    private bool $douleurAbdominale = false;

    #[ORM\Column(options: ['default' => false])]
    private bool $fatigue = false;

    #[ORM\Column(options: ['default' => false])]
    private bool $vertiges = false;

    #[ORM\Column(nullable: true)]
    private ?float $indiceRisque = null;

    #[ORM\Column(length: 20, nullable: true)]
private ?string $riskLevel = null;

    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true)]
    #[Assert\LessThanOrEqual('today', message: 'La date d’accouchement réelle doit être dans le passé ou aujourd’hui.')]
    private ?\DateTime $dateAccouchementReelle = null;

    #[ORM\Column(nullable: true)]
    #[Assert\Range(min: 2, max: 5, notInRangeMessage: 'Le nombre de bébés doit être entre {{ min }} et {{ max }}.')]
    private ?int $nombreBebes = null;

    #[ORM\Column(length: 200, nullable: true)]
    #[Assert\Length(min: 2, max: 50, minMessage: 'Le prénom doit contenir au moins {{ limit }} caractères.', maxMessage: 'Le prénom ne peut pas dépasser {{ limit }} caractères.')]
    private ?string $nomBebe = null;

    #[ORM\Column(length: 10, nullable: true)]
    #[Assert\Choice(choices: ['F', 'M'], message: 'Choisissez un sexe valide.')]
    private ?string $sexeBebe = null;

    #[ORM\Column(nullable: true)]
    #[Assert\Range(min: 0.5, max: 6, notInRangeMessage: 'Le poids de naissance doit être entre {{ min }} et {{ max }} kg.')]
    private ?float $poidsNaissance = null;

    #[ORM\Column(nullable: true)]
    #[Assert\Range(min: 25, max: 65, notInRangeMessage: 'La taille de naissance doit être entre {{ min }} et {{ max }} cm.')]
    private ?float $tailleNaissance = null;

    #[ORM\Column(length: 100, nullable: true)]
    #[Assert\Choice(choices: ['sain', 'premature', 'soins', 'autre'], message: 'Choisissez un état valide.')]
    private ?string $etatNaissance = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    #[Assert\Length(max: 3000, maxMessage: 'Le commentaire ne peut pas dépasser {{ limit }} caractères.')]
    private ?string $commentaireGeneral = null;

    /**
     * Données structurées pour plusieurs bébés (sans nouvelle entité).
     * Stockées en base dans un champ LONGTEXT (JSON sérialisé).
     */
    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $bebes = null;

    #[ORM\ManyToOne(inversedBy: 'grosesses')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Maman $maman = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $dateCreation = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $dateMiseAJour = null;

    #[ORM\PrePersist]
    public function onCreate(): void
    {
        // Doctrine attend un \DateTime pour DATETIME_MUTABLE
        $now = new \DateTime();
        $this->dateCreation = $now;
        $this->dateMiseAJour = $now;
    }

    #[ORM\PreUpdate]
    public function onUpdate(): void
    {
        $this->dateMiseAJour = new \DateTime();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function isConnaitDDR(): bool
    {
        return $this->connaitDDR;
    }

    public function setConnaitDDR(bool $connaitDDR): static
    {
        $this->connaitDDR = $connaitDDR;
        return $this;
    }

    public function getDateDernieresRegles(): ?\DateTime
    {
        return $this->dateDernieresRegles;
    }

    public function setDateDernieresRegles(?\DateTime $dateDernieresRegles): static
    {
        $this->dateDernieresRegles = $dateDernieresRegles;

        return $this;
    }

    public function getDateDebutGrossesse(): ?\DateTime
    {
        return $this->dateDebutGrossesse;
    }

    public function setDateDebutGrossesse(?\DateTime $dateDebutGrossesse): static
    {
        $this->dateDebutGrossesse = $dateDebutGrossesse;

        return $this;
    }

    public function getStatutGrossesse(): ?string
    {
        return $this->statutGrossesse;
    }

    public function setStatutGrossesse(string $statutGrossesse): static
    {
        $this->statutGrossesse = $statutGrossesse;

        return $this;
    }

    public function getTypeGrossesse(): ?string
    {
        return $this->typeGrossesse;
    }

    public function setTypeGrossesse(string $typeGrossesse): static
    {
        $this->typeGrossesse = $typeGrossesse;

        return $this;
    }

    public function getPoidsActuel(): ?float
    {
        return $this->poidsActuel;
    }

    public function setPoidsActuel(?float $poidsActuel): static
    {
        $this->poidsActuel = $poidsActuel;

        return $this;
    }

    public function getSymptomes(): ?string
    {
        return $this->symptomes;
    }

    public function setSymptomes(?string $symptomes): static
    {
        $this->symptomes = $symptomes;

        return $this;
    }

    public function isNausee(): bool
    {
        return $this->nausee;
    }

    public function setNausee(bool $nausee): static
    {
        $this->nausee = $nausee;
        return $this;
    }

    public function isVomissement(): bool
    {
        return $this->vomissement;
    }

    public function setVomissement(bool $vomissement): static
    {
        $this->vomissement = $vomissement;
        return $this;
    }

    public function isSaignement(): bool
    {
        return $this->saignement;
    }

    public function setSaignement(bool $saignement): static
    {
        $this->saignement = $saignement;
        return $this;
    }

    public function isFievre(): bool
    {
        return $this->fievre;
    }

    public function setFievre(bool $fievre): static
    {
        $this->fievre = $fievre;
        return $this;
    }

    public function isDouleurAbdominale(): bool
    {
        return $this->douleurAbdominale;
    }

    public function setDouleurAbdominale(bool $douleurAbdominale): static
    {
        $this->douleurAbdominale = $douleurAbdominale;
        return $this;
    }

    public function isFatigue(): bool
    {
        return $this->fatigue;
    }

    public function setFatigue(bool $fatigue): static
    {
        $this->fatigue = $fatigue;
        return $this;
    }

    public function isVertiges(): bool
    {
        return $this->vertiges;
    }

    public function setVertiges(bool $vertiges): static
    {
        $this->vertiges = $vertiges;
        return $this;
    }

    /** @return string[] Labels of symptoms that are true */
    public function getSymptomesList(): array
    {
        $labels = [];
        if ($this->nausee) {
            $labels[] = 'Nausée';
        }
        if ($this->vomissement) {
            $labels[] = 'Vomissement';
        }
        if ($this->saignement) {
            $labels[] = 'Saignement';
        }
        if ($this->fievre) {
            $labels[] = 'Fièvre';
        }
        if ($this->douleurAbdominale) {
            $labels[] = 'Douleur abdominale';
        }
        if ($this->fatigue) {
            $labels[] = 'Fatigue';
        }
        if ($this->vertiges) {
            $labels[] = 'Vertiges';
        }
        return $labels;
    }

    public function getIndiceRisque(): ?float
    {
        return $this->indiceRisque;
    }

    public function setIndiceRisque(?float $indiceRisque): static
    {
        $this->indiceRisque = $indiceRisque;

        return $this;
    }

    public function getDateAccouchementReelle(): ?\DateTime
    {
        return $this->dateAccouchementReelle;
    }

    public function setDateAccouchementReelle(?\DateTime $dateAccouchementReelle): static
    {
        $this->dateAccouchementReelle = $dateAccouchementReelle;

        return $this;
    }

    public function getNombreBebes(): ?int
    {
        return $this->nombreBebes;
    }

    public function setNombreBebes(?int $nombreBebes): static
    {
        $this->nombreBebes = $nombreBebes;

        return $this;
    }

    public function getNomBebe(): ?string
    {
        return $this->nomBebe;
    }

    public function setNomBebe(?string $nomBebe): static
    {
        $this->nomBebe = $nomBebe;

        return $this;
    }

    public function getSexeBebe(): ?string
    {
        return $this->sexeBebe;
    }

    public function setSexeBebe(?string $sexeBebe): static
    {
        $this->sexeBebe = $sexeBebe;

        return $this;
    }

    public function getPoidsNaissance(): ?float
    {
        return $this->poidsNaissance;
    }

    public function setPoidsNaissance(?float $poidsNaissance): static
    {
        $this->poidsNaissance = $poidsNaissance;

        return $this;
    }

    public function getTailleNaissance(): ?float
    {
        return $this->tailleNaissance;
    }

    public function setTailleNaissance(?float $tailleNaissance): static
    {
        $this->tailleNaissance = $tailleNaissance;

        return $this;
    }

    public function getEtatNaissance(): ?string
    {
        return $this->etatNaissance;
    }

    public function setEtatNaissance(?string $etatNaissance): static
    {
        $this->etatNaissance = $etatNaissance;

        return $this;
    }

    public function getCommentaireGeneral(): ?string
    {
        return $this->commentaireGeneral;
    }

    public function setCommentaireGeneral(?string $commentaireGeneral): static
    {
        $this->commentaireGeneral = $commentaireGeneral;
        return $this;
    }

    public function getBebes(): array
    {
        if ($this->bebes === null || $this->bebes === '') {
            return [];
        }

        $decoded = json_decode($this->bebes, true);
        return is_array($decoded) ? $decoded : [];
    }

    public function setBebes(?array $bebes): static
    {
        if ($bebes === null || $bebes === []) {
            $this->bebes = null;
        } else {
            $this->bebes = json_encode($bebes, JSON_THROW_ON_ERROR);
        }

        return $this;
    }

    public function getMaman(): ?Maman
    {
        return $this->maman;
    }

    public function setMaman(?Maman $maman): static
    {
        $this->maman = $maman;

        return $this;
    }

    public function getDateCreation(): ?\DateTimeInterface
    {
        return $this->dateCreation;
    }

    public function getDateMiseAJour(): ?\DateTimeInterface
    {
        return $this->dateMiseAJour;
    }

    public function getDateAccouchementPrevue(): ?\DateTimeImmutable
    {
        $base = $this->connaitDDR ? $this->dateDernieresRegles : $this->dateDebutGrossesse;
        if (!$base instanceof \DateTimeInterface) {
            return null;
        }
        return \DateTimeImmutable::createFromInterface($base)->modify('+280 days');
    }

    public function getSemaineActuelle(): ?int
    {
        $base = $this->connaitDDR ? $this->dateDernieresRegles : $this->dateDebutGrossesse;
        if (!$base instanceof \DateTimeInterface) {
            return null;
        }
        $today = new \DateTimeImmutable('today');
        $days = $base->diff($today)->days ?? 0;
        return max(1, (int) floor($days / 7) + 1);
    }

    public function getTrimestreActuel(): ?int
    {
        $week = $this->getSemaineActuelle();
        if ($week === null) {
            return null;
        }
        if ($week <= 13) {
            return 1;
        }
        if ($week <= 27) {
            return 2;
        }
        return 3;
    }

    #[Assert\Callback]
    public function validateBusinessRules(ExecutionContextInterface $context): void
    {
        // DDR logique
        if ($this->connaitDDR) {
            if ($this->dateDernieresRegles === null) {
                $context->buildViolation('Veuillez renseigner la date des dernières règles.')
                    ->atPath('dateDernieresRegles')
                    ->addViolation();
            }
        } else {
            if ($this->dateDebutGrossesse === null) {
                $context->buildViolation('Veuillez renseigner la date de début de grossesse si vous ne connaissez pas la DDR.')
                    ->atPath('dateDebutGrossesse')
                    ->addViolation();
            }
        }

        // Grossesse multiple → nombre bébés requis
        if ($this->typeGrossesse === 'multiple') {
            if ($this->nombreBebes === null || $this->nombreBebes < 2) {
                $context->buildViolation('Pour une grossesse multiple, indiquez le nombre de bébés (2, 3, ...).')
                    ->atPath('nombreBebes')
                    ->addViolation();
            }
        }

        // Si grossesse terminée, bloc bébé cohérent
        if ($this->statutGrossesse === 'terminee') {
            if ($this->dateAccouchementReelle === null) {
                $context->buildViolation('Veuillez renseigner la date d’accouchement réelle.')
                    ->atPath('dateAccouchementReelle')
                    ->addViolation();
            }
            if ($this->nomBebe === null || $this->nomBebe === '') {
                $context->buildViolation('Veuillez renseigner le prénom du bébé.')
                    ->atPath('nomBebe')
                    ->addViolation();
            }
        }

        // dateAccouchementReelle >= début de grossesse
        if ($this->dateAccouchementReelle instanceof \DateTimeInterface) {
            $base = $this->connaitDDR ? $this->dateDernieresRegles : $this->dateDebutGrossesse;
            if ($base instanceof \DateTimeInterface && $this->dateAccouchementReelle < $base) {
                $context->buildViolation('La date d’accouchement réelle ne peut pas être avant le début de la grossesse.')
                    ->atPath('dateAccouchementReelle')
                    ->addViolation();
            }
        }
    }
    public function getRiskLevel(): ?string
{
    return $this->riskLevel;
}

public function setRiskLevel(?string $riskLevel): static
{
    $this->riskLevel = $riskLevel;
    return $this;
}
}
