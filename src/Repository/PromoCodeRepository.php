<?php

namespace App\Repository;

use App\Entity\PromoCode;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<PromoCode>
 */
class PromoCodeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PromoCode::class);
    }

    public function findActiveForEmail(string $code, ?string $email): ?PromoCode
    {
        $qb = $this->createQueryBuilder('p')
            ->andWhere('p.code = :code')
            ->andWhere('p.isUsed = false')
            ->setParameter('code', $code);

        if ($email !== '') {
            $qb->andWhere('p.email IS NULL OR p.email = :email')
               ->setParameter('email', $email);
        }

        /** @var PromoCode|null $promo */
        $promo = $qb->getQuery()->getOneOrNullResult();

        return $promo;
    }

    public function countForEmailInMonth(string $email, \DateTimeImmutable $date): int
    {
        $start = $date->modify('first day of this month')->setTime(0, 0, 0);
        $end = $date->modify('last day of this month')->setTime(23, 59, 59);

        return (int) $this->createQueryBuilder('p')
            ->select('COUNT(p.id)')
            ->andWhere('p.email = :email')
            ->andWhere('p.createdAt BETWEEN :start AND :end')
            ->setParameter('email', $email)
            ->setParameter('start', $start)
            ->setParameter('end', $end)
            ->getQuery()
            ->getSingleScalarResult();
    }
}

