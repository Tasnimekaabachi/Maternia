<?php

namespace App\Entity;

use App\Repository\ProduitRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ProduitRepository::class)]
class Produit
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "Le nom du produit est obligatoire")]
    #[Assert\Length(min: 3, max: 255, minMessage: "Le nom doit contenir au moins 3 caractères", maxMessage: "Le nom ne peut pas dépasser 255 caractères")]
    private ?string $nom = null;

    #[ORM\Column(type: 'text')]
    #[Assert\NotBlank(message: "La description est obligatoire")]
    #[Assert\Length(max: 5000, maxMessage: "La description ne peut pas dépasser 5000 caractères")]
    private ?string $description = null;

    #[ORM\Column]
    #[Assert\NotNull(message: "Le prix est obligatoire")]
    #[Assert\Positive(message: "Le prix doit être positif")]
    #[Assert\Type(type: 'float', message: "Le prix doit être un nombre")]
    private ?float $prix = null;

    #[ORM\Column]
    #[Assert\NotNull(message: "Le stock est obligatoire")]
    #[Assert\PositiveOrZero(message: "Le stock ne peut pas être négatif")]
    #[Assert\Type(type: 'integer', message: "Le stock doit être un entier")]
    private ?int $stock = null;

    /** Slug de catégorie : grossesse, bebe, soins, mode, equipement, services */
    #[ORM\Column(length: 50, nullable: true)]
    private ?string $categorie = null;

    /** Nom du fichier image (stocké dans public/uploads/produits/) */
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $imageName = null;

    /** Poids du produit en kg (utilisé pour le calcul livraison) */
    #[ORM\Column(nullable: true)]
    #[Assert\PositiveOrZero(message: "Le poids doit être positif ou zéro")]
    #[Assert\Type(type: 'float', message: "Le poids doit être un nombre")]
    private ?float $poidsKg = null;

    /** Identifiant fournisseur / SKU (utilisé pour synchronisation) */
    #[ORM\Column(length: 64, nullable: true)]
    #[Assert\Length(max: 64)]
    private ?string $sku = null;

    #[ORM\Column(nullable: true)]
    #[Assert\PositiveOrZero(message: "La note moyenne doit être positive ou zéro")]
    #[Assert\Range(
        min: 0,
        max: 5,
        notInRangeMessage: "La note moyenne doit être comprise entre 0 et 5"
    )]
    private ?float $ratingAverage = null;

    #[ORM\Column(options: ['default' => 0])]
    #[Assert\PositiveOrZero(message: "Le nombre de notes doit être positif ou zéro")]
    private int $ratingCount = 0;

    #[ORM\ManyToMany(targetEntity: Commande::class, mappedBy: 'produits')]
    private Collection $commandes;

    public function __construct()
    {
        $this->commandes = new ArrayCollection();
    }

    /**
     * @return Collection<int, Commande>
     */
    public function getCommandes(): Collection
    {
        return $this->commandes;
    }

    public function addCommande(Commande $commande): static
    {
        if (!$this->commandes->contains($commande)) {
            $this->commandes->add($commande);
            $commande->addProduit($this);
        }

        return $this;
    }

    public function removeCommande(Commande $commande): static
    {
        if ($this->commandes->removeElement($commande)) {
            $commande->removeProduit($this);
        }

        return $this;
    }
    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function setNom(string $nom): static
    {
        $this->nom = $nom;

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

    public function getPrix(): ?float
    {
        return $this->prix;
    }

    public function setPrix(float $prix): static
    {
        $this->prix = $prix;

        return $this;
    }

    public function getStock(): ?int
    {
        return $this->stock;
    }

    public function setStock(int $stock): static
    {
        $this->stock = $stock;

        return $this;
    }

    public function getCategorie(): ?string
    {
        return $this->categorie;
    }

    public function setCategorie(?string $categorie): static
    {
        $this->categorie = $categorie;

        return $this;
    }

    public function getImageName(): ?string
    {
        return $this->imageName;
    }

    public function setImageName(?string $imageName): static
    {
        $this->imageName = $imageName;

        return $this;
    }

    public function getPoidsKg(): ?float
    {
        return $this->poidsKg;
    }

    public function setPoidsKg(?float $poidsKg): static
    {
        $this->poidsKg = $poidsKg;

        return $this;
    }

    public function getSku(): ?string
    {
        return $this->sku;
    }

    public function setSku(?string $sku): static
    {
        $this->sku = $sku;

        return $this;
    }

    public function getRatingAverage(): ?float
    {
        return $this->ratingAverage;
    }

    public function setRatingAverage(?float $ratingAverage): static
    {
        $this->ratingAverage = $ratingAverage;

        return $this;
    }

    public function getRatingCount(): int
    {
        return $this->ratingCount;
    }

    public function setRatingCount(int $ratingCount): static
    {
        $this->ratingCount = $ratingCount;

        return $this;
    }
}
