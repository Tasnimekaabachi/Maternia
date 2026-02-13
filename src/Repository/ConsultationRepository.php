<?php

namespace App\Repository;

use App\Entity\Consultation;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Consultation>
 */
class ConsultationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Consultation::class);
    }

    //    /**
    //     * @return Consultation[] Returns an array of Consultation objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('c')
    //            ->andWhere('c.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('c.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Consultation
    //    {
    //        return $this->createQueryBuilder('c')
    //            ->andWhere('c.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
    public function findByType(string $type): array
    {
        return $this->createQueryBuilder('c')
            ->where('c.statut = :statut')
            ->andWhere('c.pour = :type OR c.pour = :both')
            ->setParameter('statut', true)
            ->setParameter('type', $type)
            ->setParameter('both', 'LES_DEUX')
            ->orderBy('c.ordreAffichage', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function findActiveCategories(): array
    {
        return $this->createQueryBuilder('c')
            ->where('c.statut = :statut')
            ->setParameter('statut', true)
            ->orderBy('c.ordreAffichage', 'ASC')
            ->getQuery()
            ->getResult();
    }
     public function findAllOrdered(): array
    {
        return $this->createQueryBuilder('c')
            ->orderBy('c.ordreAffichage', 'ASC')
            ->addOrderBy('c.categorie', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /** Recherche dans catégorie + description (page publique, consultations actives uniquement) */
    public function searchActive(?string $term): array
    {
        $qb = $this->createQueryBuilder('c')
            ->where('c.statut = :statut')
            ->setParameter('statut', true)
            ->orderBy('c.ordreAffichage', 'ASC')
            ->addOrderBy('c.categorie', 'ASC');
        if ($term !== null && trim($term) !== '') {
            $qb->andWhere('c.categorie LIKE :term OR c.description LIKE :term')
                ->setParameter('term', '%' . trim($term) . '%');
        }
        return $qb->getQuery()->getResult();
    }

    /** Recherche dans catégorie + description (backoffice, toutes les consultations) */
    public function searchAll(?string $term): array
    {
        $qb = $this->createQueryBuilder('c')
            ->orderBy('c.ordreAffichage', 'ASC')
            ->addOrderBy('c.categorie', 'ASC');
        if ($term !== null && trim($term) !== '') {
            $qb->where('c.categorie LIKE :term OR c.description LIKE :term')
                ->setParameter('term', '%' . trim($term) . '%');
        }
        return $qb->getQuery()->getResult();
    }

    public function findByStatut(bool $statut): array
    {
        return $this->createQueryBuilder('c')
            ->where('c.statut = :statut')
            ->setParameter('statut', $statut)
            ->orderBy('c.ordreAffichage', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function findByPour(string $pour): array
    {
        return $this->createQueryBuilder('c')
            ->where('c.pour = :pour')
            ->setParameter('pour', $pour)
            ->andWhere('c.statut = :statut')
            ->setParameter('statut', true)
            ->orderBy('c.ordreAffichage', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function save(Consultation $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Consultation $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }


}
