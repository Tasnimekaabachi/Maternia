<?php

namespace App\Entity;

use App\Repository\DemandeBabySitterRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: DemandeBabySitterRepository::class)]
class DemandeBabySitter
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $nomParent = null;

    #[ORM\Column(length: 255)]
    private ?string $emailParent = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $message = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $dateDemande = null;

    #[ORM\Column(length: 50)]
    private ?string $statut = null;

    // 🔗 Relation avec OffreBabySitter
    #[ORM\ManyToOne(inversedBy: 'demandes')]
    #[ORM\JoinColumn(nullable: false)]
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
