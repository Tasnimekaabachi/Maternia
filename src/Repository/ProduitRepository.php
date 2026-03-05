<?php

namespace App\Repository;

use App\Entity\Produit;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Produit>
 */
class ProduitRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Produit::class);
    }
public function search(string $term)
{
    return $this->createQueryBuilder('p')
        ->where('p.nom LIKE :t')
        ->setParameter('t', '%'.$term.'%')
        ->getQuery()
        ->getResult();
}

/**
 * QueryBuilder pour catégorie + recherche (pour pagination KnpPaginator).
 */
public function getQbByCategorieAndSearch(?string $categorie, string $searchTerm = ''): \Doctrine\ORM\QueryBuilder
{
    $qb = $this->createQueryBuilder('p')
        ->orderBy('p.id', 'ASC');
    if ($categorie !== null && $categorie !== '') {
        $qb->andWhere('p.categorie = :cat')
           ->setParameter('cat', $categorie);
    }
    if ($searchTerm !== '') {
        $qb->andWhere('p.nom LIKE :t')
           ->setParameter('t', '%'.$searchTerm.'%');
    }
    return $qb;
}

/**
 * Trouve les produits par catégorie (slug), avec recherche optionnelle.
 *
 * @param string|null $categorie Slug : grossesse, bebe, soins, mode, equipement, services
 * @param string      $searchTerm Terme de recherche optionnel
 * @return Produit[]
 */
public function findByCategorieAndSearch(?string $categorie, string $searchTerm = ''): array
{
    return $this->getQbByCategorieAndSearch($categorie, $searchTerm)
        ->getQuery()
        ->getResult();
}

    //    /**
    //     * @return Produit[] Returns an array of Produit objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('p')
    //            ->andWhere('p.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('p.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Produit
    //    {
    //        return $this->createQueryBuilder('p')
    //            ->andWhere('p.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
