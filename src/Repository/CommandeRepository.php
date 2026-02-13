<?php

namespace App\Repository;

use App\Entity\Commande;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Commande>
 */
class CommandeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Commande::class);
    }

    public function countByStatut(string $statut): int
    {
        return (int) $this->createQueryBuilder('c')
            ->select('COUNT(c.id)')
            ->andWhere('c.statut = :statut')
            ->setParameter('statut', $statut)
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function chiffreAffairesValidees(): float
    {
        $result = $this->createQueryBuilder('c')
            ->select('SUM(c.total)')
            ->andWhere('c.statut = :statut')
            ->setParameter('statut', 'Validée')
            ->getQuery()
            ->getSingleScalarResult();

        return (float) ($result ?? 0);
    }

    /**
     * @return array<array{produit: string, nb: int}>
     */
    public function topProduitsCommandes(int $limit = 5): array
    {
        $conn = $this->getEntityManager()->getConnection();
        $sql = '
            SELECT p.nom AS produit, COUNT(*) AS nb
            FROM commande_produit cp
            JOIN produit p ON p.id = cp.produit_id
            JOIN commande c ON c.id = cp.commande_id AND c.statut = :statut
            GROUP BY p.id, p.nom
            ORDER BY nb DESC
            LIMIT ' . (int) $limit . '
        ';
        $result = $conn->executeQuery($sql, ['statut' => 'Validée']);

        return $result->fetchAllAssociative();
    }

    //    /**
    //     * @return Commande[] Returns an array of Commande objects
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

    //    public function findOneBySomeField($value): ?Commande
    //    {
    //        return $this->createQueryBuilder('c')
    //            ->andWhere('c.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
