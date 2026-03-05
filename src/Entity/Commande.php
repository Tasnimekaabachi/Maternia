<?php

namespace App\Entity;

use App\Repository\CommandeRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: CommandeRepository::class)]
class Commande
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: 'datetime')]
    #[Assert\NotNull(message: "La date de commande est obligatoire")]
    private ?\DateTimeInterface $dateCommande = null;

    #[ORM\Column(length: 50)]
    #[Assert\NotBlank(message: "Le statut est obligatoire")]
    #[Assert\Choice(
        choices: ['En attente', 'Validée', 'Annulée'],
        message: "Statut invalide"
    )]
    #[Assert\Length(max: 50)]
    private ?string $statut = null;

    #[ORM\Column]
    #[Assert\NotNull(message: "Le total est obligatoire")]
    #[Assert\PositiveOrZero(message: "Le total doit être positif ou zéro")]
    #[Assert\Type(type: 'float', message: "Le total doit être un nombre")]
    private ?float $total = 0;

    #[ORM\Column(length: 180, nullable: true)]
    #[Assert\Email(message: "L’email n’est pas valide.")]
    private ?string $email = null;

    #[ORM\Column(length: 30, nullable: true)]
    private ?string $telephone = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $shippingAddress = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $shippingCity = null;

    #[ORM\Column(length: 20, nullable: true)]
    private ?string $shippingPostalCode = null;

    #[ORM\Column(length: 2, nullable: true)]
    private ?string $shippingCountry = null;

    #[ORM\Column(nullable: true)]
    #[Assert\PositiveOrZero(message: "Les frais de livraison doivent être positifs ou zéro")]
    private ?float $shippingCost = null;

    #[ORM\Column(length: 60, nullable: true)]
    private ?string $shippingCarrier = null;

    #[ORM\Column(nullable: true)]
    #[Assert\PositiveOrZero(message: "Le délai estimé doit être positif ou zéro")]
    private ?int $shippingEtaDays = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $shippingTracking = null;

    #[ORM\Column(length: 30, nullable: true)]
    private ?string $paymentStatus = null;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private ?\DateTimeInterface $paidAt = null;

    #[ORM\ManyToMany(targetEntity: Produit::class, inversedBy: 'commandes')]
    #[ORM\JoinTable(name: 'commande_produit')]
    private Collection $produits;

    public function __construct()
    {
        $this->produits = new ArrayCollection();
        $this->dateCommande = new \DateTime();
        $this->statut = 'En attente';
    }
    /**
     * @return Collection<int, Produit>
     */
    public function getProduits(): Collection
    {
        return $this->produits;
    }

    public function addProduit(Produit $produit): static
    {
        if (!$this->produits->contains($produit)) {
            $this->produits->add($produit);
            $produit->addCommande($this);
        }

        return $this;
    }

    public function removeProduit(Produit $produit): static
    {
        if ($this->produits->removeElement($produit)) {
            $produit->removeCommande($this);
        }

        return $this;
    }
    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDateCommande(): ?\DateTime
    {
        return $this->dateCommande;
    }

    public function setDateCommande(\DateTime $dateCommande): static
    {
        $this->dateCommande = $dateCommande;

        return $this;
    }

    public function getStatut(): ?string
    {
        return $this->statut;
    }

    public function setStatut(string $statut): static
    {
        $this->statut = $statut;

        return $this;
    }

    public function getTotal(): ?float
    {
        return $this->total;
    }

    public function setTotal(float $total): static
    {
        $this->total = $total;

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

    public function getTelephone(): ?string
    {
        return $this->telephone;
    }

    public function setTelephone(?string $telephone): static
    {
        $this->telephone = $telephone;

        return $this;
    }

    public function getShippingAddress(): ?string
    {
        return $this->shippingAddress;
    }

    public function setShippingAddress(?string $shippingAddress): static
    {
        $this->shippingAddress = $shippingAddress;

        return $this;
    }

    public function getShippingCity(): ?string
    {
        return $this->shippingCity;
    }

    public function setShippingCity(?string $shippingCity): static
    {
        $this->shippingCity = $shippingCity;

        return $this;
    }

    public function getShippingPostalCode(): ?string
    {
        return $this->shippingPostalCode;
    }

    public function setShippingPostalCode(?string $shippingPostalCode): static
    {
        $this->shippingPostalCode = $shippingPostalCode;

        return $this;
    }

    public function getShippingCountry(): ?string
    {
        return $this->shippingCountry;
    }

    public function setShippingCountry(?string $shippingCountry): static
    {
        $this->shippingCountry = $shippingCountry;

        return $this;
    }

    public function getShippingCost(): ?float
    {
        return $this->shippingCost;
    }

    public function setShippingCost(?float $shippingCost): static
    {
        $this->shippingCost = $shippingCost;

        return $this;
    }

    public function getShippingCarrier(): ?string
    {
        return $this->shippingCarrier;
    }

    public function setShippingCarrier(?string $shippingCarrier): static
    {
        $this->shippingCarrier = $shippingCarrier;

        return $this;
    }

    public function getShippingEtaDays(): ?int
    {
        return $this->shippingEtaDays;
    }

    public function setShippingEtaDays(?int $shippingEtaDays): static
    {
        $this->shippingEtaDays = $shippingEtaDays;

        return $this;
    }

    public function getShippingTracking(): ?string
    {
        return $this->shippingTracking;
    }

    public function setShippingTracking(?string $shippingTracking): static
    {
        $this->shippingTracking = $shippingTracking;

        return $this;
    }

    public function getPaymentStatus(): ?string
    {
        return $this->paymentStatus;
    }

    public function setPaymentStatus(?string $paymentStatus): static
    {
        $this->paymentStatus = $paymentStatus;

        return $this;
    }

    public function getPaidAt(): ?\DateTimeInterface
    {
        return $this->paidAt;
    }

    public function setPaidAt(?\DateTimeInterface $paidAt): static
    {
        $this->paidAt = $paidAt;

        return $this;
    }
}
