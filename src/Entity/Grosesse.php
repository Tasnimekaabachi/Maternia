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
    private ?\DateTime $dateDernieresRegles = null;

    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true)]
    private ?\DateTime $dateDebutGrossesse = null;

    #[ORM\Column(length: 50)]
    private ?string $statutGrossesse = null;

    #[ORM\Column(length: 50)]
    private ?string $typeGrossesse = null;

    #[ORM\Column(nullable: true)]
    private ?float $poidsActuel = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $symptomes = null;

    #[ORM\Column(nullable: true)]
    private ?float $indiceRisque = null;

    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true)]
    private ?\DateTime $dateAccouchementReelle = null;

    #[ORM\Column(nullable: true)]
    private ?int $nombreBebes = null;

    #[ORM\Column(length: 200, nullable: true)]
    private ?string $nomBebe = null;

    #[ORM\Column(length: 50, nullable: true)]
    private ?string $sexeBebe = null;

    #[ORM\Column(nullable: true)]
    private ?float $poidsNaissance = null;

    #[ORM\Column(nullable: true)]
    private ?float $tailleNaissance = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $etatNaissance = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $commentaireGeneral = null;

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
    }
}
