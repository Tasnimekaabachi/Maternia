<?php

namespace App\Entity;

use App\Repository\PromoCodeRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PromoCodeRepository::class)]
class PromoCode
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 64, unique: true)]
    private string $code;

    #[ORM\Column]
    private int $discountPercent;

    #[ORM\Column(length: 180, nullable: true)]
    private ?string $email = null;

    #[ORM\Column(options: ['default' => false])]
    private bool $isUsed = false;

    #[ORM\Column(type: 'datetime_immutable')]
    private \DateTimeImmutable $createdAt;

    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    private ?\DateTimeImmutable $usedAt = null;

    public function __construct(string $code, int $discountPercent, ?string $email = null)
    {
        $this->code = $code;
        $this->discountPercent = $discountPercent;
        $this->email = $email;
        $this->createdAt = new \DateTimeImmutable();
        $this->isUsed = false;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCode(): string
    {
        return $this->code;
    }

    public function getDiscountPercent(): int
    {
        return $this->discountPercent;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function isUsed(): bool
    {
        return $this->isUsed;
    }

    public function markUsed(): void
    {
        $this->isUsed = true;
        $this->usedAt = new \DateTimeImmutable();
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getUsedAt(): ?\DateTimeImmutable
    {
        return $this->usedAt;
    }
}

