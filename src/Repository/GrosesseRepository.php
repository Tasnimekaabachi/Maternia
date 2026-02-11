<?php

namespace App\Repository;

use App\Entity\Grosesse;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Grosesse>
 */
class GrosesseRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Grosesse::class);
    }

    public function save(Grosesse $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Grosesse $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * Liste des grossesses triées par priorité/urgence pour l'admin.
     * Ordre :
     *  - grossesses à risque d'abord
     *  - puis en cours
     *  - terminées à la fin
     *  À priorité égale, les plus avancées (dateDebut la plus ancienne) d'abord.
     *
     * @return Grosesse[]
     */
    public function findForAdminSorted(): array
    {
        $qb = $this->createQueryBuilder('g')
            ->addSelect(
                "CASE 
                    WHEN g.statutGrossesse = 'aRisque' THEN 1
                    WHEN g.statutGrossesse = 'enCours' THEN 2
                    ELSE 3
                END AS HIDDEN priority"
            )
            ->orderBy('priority', 'ASC')
            ->addOrderBy('g.dateDebutGrossesse', 'DESC')
            ->addOrderBy('g.dateDernieresRegles', 'DESC')
        ;

        return $qb->getQuery()->getResult();
    }

    /**
     * Statistiques par statut (enCours, aRisque, terminee) pour dashboard admin.
     *
     * @return array<string,int>
     */
    public function getStatsByStatut(): array
    {
        $qb = $this->createQueryBuilder('g')
            ->select('g.statutGrossesse AS statut, COUNT(g.id) AS total')
            ->groupBy('g.statutGrossesse')
        ;

        $rows = $qb->getQuery()->getScalarResult();

        $result = [
            'enCours' => 0,
            'aRisque' => 0,
            'terminee' => 0,
        ];

        foreach ($rows as $row) {
            $statut = $row['statut'] ?? null;
            if ($statut && array_key_exists($statut, $result)) {
                $result[$statut] = (int) $row['total'];
            }
        }

        return $result;
    }

    //    /**
    //     * @return Grosesse[] Returns an array of Grosesse objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('g')
    //            ->andWhere('g.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('g.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Grosesse
    //    {
    //        return $this->createQueryBuilder('g')
    //            ->andWhere('g.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
