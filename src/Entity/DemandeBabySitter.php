<?php

namespace App\Entity;

use App\Repository\DemandeBabySitterRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: DemandeBabySitterRepository::class)]
class DemandeBabySitter
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: 'Le nom du parent est obligatoire.')]
    #[Assert\Length(
        min: 3,
        max: 255,
        minMessage: 'Le nom doit contenir au minimum {{ limit }} caractères.',
        maxMessage: 'Le nom doit contenir au maximum {{ limit }} caractères.'
    )]
    private ?string $nomParent = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: 'L\'email du parent est obligatoire.')]
    #[Assert\Email(message: 'L\'adresse email n\'est pas valide.')]
    private ?string $emailParent = null;

    #[ORM\Column(type: Types::TEXT)]
    #[Assert\NotBlank(message: 'Le message est obligatoire.')]
    #[Assert\Length(
        min: 10,
        minMessage: 'Le message doit contenir au minimum {{ limit }} caractères.'
    )]
    private ?string $message = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    #[Assert\NotNull(message: 'La date de demande est obligatoire.')]
    private ?\DateTimeInterface $dateDemande = null;

    #[ORM\Column(length: 50)]
    #[Assert\NotBlank(message: 'Le statut est obligatoire.')]
    private ?string $statut = null;

    // 🔗 Relation avec OffreBabySitter
    #[ORM\ManyToOne(inversedBy: 'demandes')]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotNull(message: 'L\'offre associée est obligatoire.')]
    private ?OffreBabySitter $offre = null;

    // ---------------- GETTERS & SETTERS ----------------

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNomParent(): ?string
    {
        return $this->nomParent;
    }

    public function setNomParent(string $nomParent): self
    {
        $this->nomParent = $nomParent;
        return $this;
    }

    public function getEmailParent(): ?string
    {
        return $this->emailParent;
    }

    public function setEmailParent(string $emailParent): self
    {
        $this->emailParent = $emailParent;
        return $this;
    }

    public function getMessage(): ?string
    {
        return $this->message;
    }

    public function setMessage(string $message): self
    {
        $this->message = $message;
        return $this;
    }

    public function getDateDemande(): ?\DateTimeInterface
    {
        return $this->dateDemande;
    }

    public function setDateDemande(\DateTimeInterface $dateDemande): self
    {
        $this->dateDemande = $dateDemande;
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

    public function getOffre(): ?OffreBabySitter
    {
        return $this->offre;
    }

    public function setOffre(?OffreBabySitter $offre): self
    {
        $this->offre = $offre;
        return $this;
    }
}
