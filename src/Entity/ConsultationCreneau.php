<?php

namespace App\Entity;

use App\Repository\ConsultationCreneauRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ConsultationCreneauRepository::class)]
class ConsultationCreneau
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'consultationCreneaus')]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotNull(message: "Veuillez sélectionner une consultation.")]
    private ?Consultation $consultation = null;

    #[ORM\Column(length: 100)]
    #[Assert\NotBlank(message: "Le nom du médecin est obligatoire.")]
    private ?string $nomMedecin = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $photoMedecin = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $descriptionMedecin = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $specialiteMedecin = null;

    #[ORM\Column(nullable: true)]
    #[Assert\NotNull(message: "La date de début est requise.")]
    private ?\DateTime $dateDebut = null;

    #[ORM\Column(nullable: true)]
    #[Assert\NotNull(message: "La date de fin est requise.")]
    private ?\DateTime $dateFin = null;

    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $jour = null;

    #[ORM\Column(type: Types::TIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $heureDebut = null;

    #[ORM\Column(type: Types::TIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $heureFin = null;


    #[ORM\OneToOne(mappedBy: 'consultationCreneau', cascade: ['persist', 'remove'])]
    private ?ReservationClient $reservation = null;

    #[ORM\Column(length: 20)]
    #[Assert\NotBlank(message: "Le statut est obligatoire.")]
    private ?string $statutReservation = null;

    #[ORM\Column(nullable: true)]
    private ?int $dureeMinutes = 30;

    #[ORM\Column(nullable: true)]
    private ?int $nombrePlaces = 1;

    #[ORM\Column(nullable: true)]
    private ?\DateTime $createdAt = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTime $updatedAt = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getConsultation(): ?Consultation
    {
        return $this->consultation;
    }

    public function setConsultation(?Consultation $consultation): static
    {
        $this->consultation = $consultation;

        return $this;
    }

    public function getNomMedecin(): ?string
    {
        return $this->nomMedecin;
    }

    public function setNomMedecin(string $nomMedecin): static
    {
        $this->nomMedecin = $nomMedecin;

        return $this;
    }

    public function getPhotoMedecin(): ?string
    {
        return $this->photoMedecin;
    }

    public function setPhotoMedecin(?string $photoMedecin): static
    {
        $this->photoMedecin = $photoMedecin;

        return $this;
    }

    public function getDescriptionMedecin(): ?string
    {
        return $this->descriptionMedecin;
    }

    public function setDescriptionMedecin(?string $descriptionMedecin): static
    {
        $this->descriptionMedecin = $descriptionMedecin;

        return $this;
    }

    public function getSpecialiteMedecin(): ?string
    {
        return $this->specialiteMedecin;
    }

    public function setSpecialiteMedecin(?string $specialiteMedecin): static
    {
        $this->specialiteMedecin = $specialiteMedecin;

        return $this;
    }

    public function getDateDebut(): ?\DateTime
    {
        return $this->dateDebut;
    }

    public function setDateDebut(?\DateTime $dateDebut): static
    {
        $this->dateDebut = $dateDebut;
        if ($dateDebut) {
            $this->jour = \DateTime::createFromFormat('Y-m-d', $dateDebut->format('Y-m-d'));
            $this->heureDebut = \DateTime::createFromFormat('H:i:s', $dateDebut->format('H:i:s'));
        }
        return $this;
    }

    public function getDateFin(): ?\DateTime
    {
        return $this->dateFin;
    }

    public function setDateFin(?\DateTime $dateFin): static
    {
        $this->dateFin = $dateFin;
        if ($dateFin) {
            $this->heureFin = \DateTime::createFromFormat('H:i:s', $dateFin->format('H:i:s'));
        }
        return $this;
    }

    public function getJour(): ?\DateTimeInterface
    {
        return $this->jour;
    }

    public function setJour(?\DateTimeInterface $jour): static
    {
        $this->jour = $jour;
        $this->syncDates();
        return $this;
    }

    public function getHeureDebut(): ?\DateTimeInterface
    {
        return $this->heureDebut;
    }

    public function setHeureDebut(?\DateTimeInterface $heureDebut): static
    {
        $this->heureDebut = $heureDebut;
        $this->syncDates();
        return $this;
    }

    public function getHeureFin(): ?\DateTimeInterface
    {
        return $this->heureFin;
    }

    public function setHeureFin(?\DateTimeInterface $heureFin): static
    {
        $this->heureFin = $heureFin;
        $this->syncDates();
        return $this;
    }

    private function syncDates(): void
    {
        if ($this->jour) {
            if ($this->heureDebut) {
                // Créer un nouvel objet DateTime à partir du jour et de l'heure
                $dtDebut = \DateTime::createFromInterface($this->jour);
                $dtDebut->setTime(
                    (int) $this->heureDebut->format('H'),
                    (int) $this->heureDebut->format('i'),
                    (int) $this->heureDebut->format('s')
                );
                $this->dateDebut = $dtDebut;
            }
            if ($this->heureFin) {
                // Créer un nouvel objet DateTime à partir du jour et de l'heure
                $dtFin = \DateTime::createFromInterface($this->jour);
                $dtFin->setTime(
                    (int) $this->heureFin->format('H'),
                    (int) $this->heureFin->format('i'),
                    (int) $this->heureFin->format('s')
                );
                $this->dateFin = $dtFin;
            }
        }
    }

    public function getReservation(): ?ReservationClient
    {
        return $this->reservation;
    }

    public function setReservation(?ReservationClient $reservation): static
    {
        // set the owning side of the relation if necessary
        if ($reservation && $reservation->getConsultationCreneau() !== $this) {
            $reservation->setConsultationCreneau($this);
        }

        $this->reservation = $reservation;

        return $this;
    }

    public function getStatutReservation(): ?string
    {
        return $this->statutReservation;
    }

    public function setStatutReservation(string $statutReservation): static
    {
        $this->statutReservation = $statutReservation;

        return $this;
    }

    public function getDureeMinutes(): ?int
    {
        return $this->dureeMinutes;
    }

    public function setDureeMinutes(?int $dureeMinutes): static
    {
        $this->dureeMinutes = $dureeMinutes;

        return $this;
    }

    public function getNombrePlaces(): ?int
    {
        return $this->nombrePlaces;
    }

    public function setNombrePlaces(?int $nombrePlaces): static
    {
        $this->nombrePlaces = $nombrePlaces;

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


}
