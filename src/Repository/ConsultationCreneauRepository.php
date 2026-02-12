<?php

namespace App\Repository;

use App\Entity\ConsultationCreneau;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class ConsultationCreneauRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ConsultationCreneau::class);
    }

    public function findAllOrdered(): array
    {
        return $this->createQueryBuilder('c')
            ->leftJoin('c.consultation', 'cons')
            ->orderBy('c.dateDebut', 'DESC')
            ->addOrderBy('cons.categorie', 'ASC')
            ->addOrderBy('c.statutReservation', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function findCreneauxDisponibles($consultationId): array
    {
        return $this->createQueryBuilder('c')
            ->where('c.consultation = :consultation')
            ->andWhere('c.statutReservation = :statut')
            ->andWhere('c.dateDebut > :now')
            ->setParameter('consultation', $consultationId)
            ->setParameter('statut', 'DISPONIBLE')
            ->setParameter('now', new \DateTime())
            ->orderBy('c.dateDebut', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function findCreneauxAujourdhui(): array
    {
        $todayStart = new \DateTime('today');
        $todayEnd = new \DateTime('tomorrow');
        
        return $this->createQueryBuilder('c')
            ->where('c.dateDebut >= :todayStart')
            ->andWhere('c.dateDebut < :todayEnd')
            ->setParameter('todayStart', $todayStart)
            ->setParameter('todayEnd', $todayEnd)
            ->orderBy('c.dateDebut', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function findCreneauxReserves(): array
    {
        return $this->createQueryBuilder('c')
            ->where('c.statutReservation != :statut')
            ->setParameter('statut', 'DISPONIBLE')
            ->orderBy('c.dateDebut', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function findCreneauxParConsultation($consultationId): array
    {
        return $this->createQueryBuilder('c')
            ->where('c.consultation = :consultation')
            ->setParameter('consultation', $consultationId)
            ->orderBy('c.dateDebut', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function countCeMois(): int
    {
        $debut = (new \DateTime())->modify('first day of this month')->setTime(0, 0);
        $fin = (new \DateTime())->modify('last day of this month')->setTime(23, 59, 59);

        return (int) $this->createQueryBuilder('c')
            ->select('COUNT(c.id)')
            ->where('c.dateDebut >= :debut')
            ->andWhere('c.dateDebut <= :fin')
            ->setParameter('debut', $debut)
            ->setParameter('fin', $fin)
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function findRecents(int $limit = 5): array
    {
        return $this->createQueryBuilder('c')
            ->leftJoin('c.consultation', 'cons')
            ->orderBy('c.dateDebut', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    /** Recherche par nom médecin ou catégorie de consultation */
    public function searchAllOrdered(?string $term): array
    {
        $qb = $this->createQueryBuilder('c')
            ->leftJoin('c.consultation', 'cons')
            ->orderBy('c.dateDebut', 'DESC')
            ->addOrderBy('cons.categorie', 'ASC')
            ->addOrderBy('c.statutReservation', 'ASC');
        if ($term !== null && trim($term) !== '') {
            $qb->andWhere('c.nomMedecin LIKE :term OR cons.categorie LIKE :term')
                ->setParameter('term', '%' . trim($term) . '%');
        }
        return $qb->getQuery()->getResult();
    }

    public function save(ConsultationCreneau $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(ConsultationCreneau $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
}