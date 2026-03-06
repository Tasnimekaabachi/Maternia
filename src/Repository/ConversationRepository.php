<?php

namespace App\Repository;

use App\Entity\Conversation;
use App\Entity\OffreBabySitter;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Conversation>
 */
class ConversationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Conversation::class);
    }

    /**
     * Conversations pour un parent (par email).
     *
     * @return Conversation[]
     */
    public function findByParentEmail(string $email): array
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.parentEmail = :email')
            ->setParameter('email', $email)
            ->orderBy('c.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Conversations pour une offre (babysitter).
     *
     * @return Conversation[]
     */
    public function findByOffre(OffreBabySitter $offre): array
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.offre = :offre')
            ->setParameter('offre', $offre)
            ->orderBy('c.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Trouve une conversation entre une offre et un parent (email).
     */
    public function findOneByOffreAndParent(OffreBabySitter $offre, string $parentEmail): ?Conversation
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.offre = :offre')
            ->andWhere('c.parentEmail = :email')
            ->setParameter('offre', $offre)
            ->setParameter('email', $parentEmail)
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
