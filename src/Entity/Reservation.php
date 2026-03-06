<?php

namespace App\Entity;

use App\Repository\ReservationRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ReservationRepository::class)]
#[ORM\Table(name: 'reservation')]
class Reservation
{
    public const STATUT_DEMANDE = 'demande';
    public const STATUT_ACCEPTEE = 'acceptee';
    public const STATUT_REFUSEE = 'refusee';
    public const STATUT_ANNULEE = 'annulee';

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: OffreBabySitter::class, inversedBy: 'reservations')]
    #[ORM\JoinColumn(nullable: false)]
    private ?OffreBabySitter $offre = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank]
    #[Assert\Email]
    private ?string $parentEmail = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank]
    #[Assert\Length(min: 2, max: 255)]
    private ?string $parentName = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    #[Assert\NotNull]
    private ?\DateTimeInterface $dateDebut = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    #[Assert\NotNull]
    #[Assert\GreaterThan(propertyPath: 'dateDebut')]
    private ?\DateTimeInterface $dateFin = null;

    #[ORM\Column(length: 20)]
    #[Assert\Choice(choices: [self::STATUT_DEMANDE, self::STATUT_ACCEPTEE, self::STATUT_REFUSEE, self::STATUT_ANNULEE])]
    private ?string $statut = self::STATUT_DEMANDE;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $message = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private ?\DateTimeImmutable $createdAt = null;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getOffre(): ?OffreBabySitter
    {
        return $this->offre;
    }

    public function setOffre(?OffreBabySitter $offre): self
    {
        $this->offre = $offre;
        return $this;
    }

    public function getParentEmail(): ?string
    {
        return $this->parentEmail;
    }

    public function setParentEmail(string $parentEmail): self
    {
        $this->parentEmail = $parentEmail;
        return $this;
    }

    public function getParentName(): ?string
    {
        return $this->parentName;
    }

    public function setParentName(string $parentName): self
    {
        $this->parentName = $parentName;
        return $this;
    }

    public function getDateDebut(): ?\DateTimeInterface
    {
        return $this->dateDebut;
    }

    public function setDateDebut(\DateTimeInterface $dateDebut): self
    {
        $this->dateDebut = $dateDebut;
        return $this;
    }

    public function getDateFin(): ?\DateTimeInterface
    {
        return $this->dateFin;
    }

    public function setDateFin(\DateTimeInterface $dateFin): self
    {
        $this->dateFin = $dateFin;
        return $this;
    }

    public function getStatut(): ?string
    {
        return $this->statut;
    }

    public function setStatut(string $statut): self
    {
        $this->statut = $statut;
        return $this;
    }

    public function getMessage(): ?string
    {
        return $this->message;
    }

    public function setMessage(?string $message): self
    {
        $this->message = $message;
        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): self
    {
        $this->createdAt = $createdAt;
        return $this;
    }
}
