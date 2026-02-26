<?php

namespace App\Entity;

use App\Repository\EventRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

#[ORM\Entity(repositoryClass: EventRepository::class)]
#[Assert\Callback(callback: 'validateTiming')]
class Event
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "Le titre est obligatoire")]
    #[Assert\Length(min: 5, minMessage: "Le titre doit faire au moins {{ limit }} caractères")]
    private ?string $title = null;

    #[ORM\Column(type: Types::TEXT)]
    #[Assert\NotBlank(message: "La description est obligatoire")]
    #[Assert\Length(min: 10, minMessage: "La description doit faire au moins {{ limit }} caractères")]
    private ?string $description = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTime $startAt = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTime $endAt = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "Le lieu est obligatoire")]
    private ?string $location = null;

    #[ORM\ManyToOne(inversedBy: 'events')]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotNull(message: "Veuillez sélectionner une catégorie")]
    private ?EventCat $eventCat = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $image = null;

    #[ORM\Column(type: Types::BOOLEAN)]
    private bool $isWeekly = false;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: true)]
    private ?User $creator = null;

    #[ORM\Column(type: Types::INTEGER, nullable: false)]
    #[Assert\NotBlank(message: "La capacité est obligatoire")]
    #[Assert\GreaterThan(1, message: "La capacité doit être d'au moins 2 participants")]
    private ?int $capacity = null;

    /**
     * @var Collection<int, Attendance>
     */
    #[ORM\OneToMany(targetEntity: Attendance::class, mappedBy: 'event', cascade: ['remove'])]
    private Collection $attendances;

    #[ORM\Column(length: 20, nullable: true)]
    private ?string $dayOfWeek = null;

    #[ORM\Column(type: Types::TIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $startTime = null;

    #[ORM\Column(type: Types::TIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $endTime = null;

    #[ORM\ManyToMany(targetEntity: Requirement::class, inversedBy: 'events')]
    #[ORM\JoinTable(name: 'event_requirement')]
    private Collection $requirements;

    #[ORM\Column(options: ["default" => false])]
    private ?bool $isOutdoor = false;

    public function __construct()
    {
        $this->requirements = new ArrayCollection();
        $this->attendances = new ArrayCollection();
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

    public function getStartAt(): ?\DateTime
    {
        return $this->startAt;
    }

    public function setStartAt(?\DateTime $startAt): static
    {
        $this->startAt = $startAt;

        return $this;
    }

    public function getEndAt(): ?\DateTime
    {
        return $this->endAt;
    }

    public function setEndAt(?\DateTime $endAt): static
    {
        $this->endAt = $endAt;

        return $this;
    }

    public function getLocation(): ?string
    {
        return $this->location;
    }

    public function setLocation(string $location): static
    {
        $this->location = $location;

        return $this;
    }

    public function getEventCat(): ?EventCat
    {
        return $this->eventCat;
    }

    public function setEventCat(?EventCat $eventCat): static
    {
        $this->eventCat = $eventCat;

        return $this;
    }

    public function isWeekly(): bool
    {
        return $this->isWeekly;
    }

    public function setIsWeekly(bool $isWeekly): static
    {
        $this->isWeekly = $isWeekly;

        return $this;
    }

    public function getCreator(): ?User
    {
        return $this->creator;
    }

    public function setCreator(?User $creator): static
    {
        $this->creator = $creator;

        return $this;
    }

    public function getCapacity(): ?int
    {
        return $this->capacity;
    }

    public function setCapacity(?int $capacity): static
    {
        $this->capacity = $capacity;

        return $this;
    }

    /**
     * @return Collection<int, Requirement>
     */
    public function getRequirements(): Collection
    {
        return $this->requirements;
    }

    public function addRequirement(Requirement $requirement): static
    {
        if (!$this->requirements->contains($requirement)) {
            $this->requirements->add($requirement);
        }

        return $this;
    }

    public function removeRequirement(Requirement $requirement): static
    {
        $this->requirements->removeElement($requirement);

        return $this;
    }

    /**
     * @return Collection<int, Attendance>
     */
    public function getAttendances(): Collection
    {
        return $this->attendances;
    }

    public function addAttendance(Attendance $attendance): static
    {
        if (!$this->attendances->contains($attendance)) {
            $this->attendances->add($attendance);
            $attendance->setEvent($this);
        }

        return $this;
    }

    public function removeAttendance(Attendance $attendance): static
    {
        if ($this->attendances->removeElement($attendance)) {
            if ($attendance->getEvent() === $this) {
                $attendance->setEvent(null);
            }
        }

        return $this;
    }

    public function getDayOfWeek(): ?string
    {
        return $this->dayOfWeek;
    }

    public function setDayOfWeek(?string $dayOfWeek): static
    {
        $this->dayOfWeek = $dayOfWeek;

        return $this;
    }

    public function getStartTime(): ?\DateTimeInterface
    {
        return $this->startTime;
    }

    public function setStartTime(?\DateTimeInterface $startTime): static
    {
        $this->startTime = $startTime;

        return $this;
    }

    public function getEndTime(): ?\DateTimeInterface
    {
        return $this->endTime;
    }

    public function setEndTime(?\DateTimeInterface $endTime): static
    {
        $this->endTime = $endTime;

        return $this;
    }

    public function isOutdoor(): ?bool
    {
        return $this->isOutdoor;
    }

    public function setIsOutdoor(bool $isOutdoor): self
    {
        $this->isOutdoor = $isOutdoor;
        return $this;
    }

    public function validateTiming(ExecutionContextInterface $context, $payload): void
    {
        if ($this->isWeekly === true) {
            if (!$this->dayOfWeek) {
                $context->buildViolation('Veuillez sélectionner un jour de la semaine pour un événement hebdomadaire.')
                    ->atPath('dayOfWeek')
                    ->addViolation();
            }
            if (!$this->startTime) {
                $context->buildViolation('L\'heure de début est obligatoire pour un événement hebdomadaire.')
                    ->atPath('startTime')
                    ->addViolation();
            }
            if (!$this->endTime) {
                $context->buildViolation('L\'heure de fin est obligatoire pour un événement hebdomadaire.')
                    ->atPath('endTime')
                    ->addViolation();
            }
        } else {
            if (!$this->startAt) {
                $context->buildViolation('La date et l\'heure de début sont obligatoires.')
                    ->atPath('startAt')
                    ->addViolation();
            }
            if (!$this->endAt) {
                $context->buildViolation('La date et l\'heure de fin sont obligatoires.')
                    ->atPath('endAt')
                    ->addViolation();
            }
        }
    }
}
