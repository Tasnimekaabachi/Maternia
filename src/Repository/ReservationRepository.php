<?php

namespace App\Repository;

use App\Entity\OffreBabySitter;
use App\Entity\Reservation;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Reservation>
 */
class ReservationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Reservation::class);
    }

    /**
     * Réservations pour un parent (email).
     *
     * @return Reservation[]
     */
    public function findByParentEmail(string $email): array
    {
        return $this->createQueryBuilder('r')
            ->andWhere('r.parentEmail = :email')
            ->setParameter('email', $email)
            ->orderBy('r.dateDebut', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Réservations pour une offre (babysitter).
     *
     * @return Reservation[]
     */
    public function findByOffre(OffreBabySitter $offre): array
    {
        return $this->createQueryBuilder('r')
            ->andWhere('r.offre = :offre')
            ->setParameter('offre', $offre)
            ->orderBy('r.dateDebut', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Réservations dans une plage de dates (pour le calendrier).
     *
     * @return Reservation[]
     */
    public function findByDateRange(\DateTimeInterface $debut, \DateTimeInterface $fin, ?int $offreId = null): array
    {
        $qb = $this->createQueryBuilder('r')
            ->andWhere('r.dateDebut <= :fin')
            ->andWhere('r.dateFin >= :debut')
            ->setParameter('debut', $debut)
            ->setParameter('fin', $fin)
            ->orderBy('r.dateDebut', 'ASC');
        if ($offreId !== null) {
            $qb->andWhere('r.offre = :offreId')->setParameter('offreId', $offreId);
        }
        return $qb->getQuery()->getResult();
    }
}
