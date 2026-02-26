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
     * aRisque → enCours (trimestre DESC, semaine DESC) → terminee
     *
     * @return Grosesse[]
     */
    public function findForAdminSorted(): array
    {
        $results = $this->createQueryBuilder('g')
            ->addSelect(
                "CASE 
                    WHEN g.statutGrossesse = 'aRisque' THEN 1
                    WHEN g.statutGrossesse = 'enCours' THEN 2
                    ELSE 3
                END AS HIDDEN priority"
            )
            ->orderBy('priority', 'ASC')
            ->getQuery()
            ->getResult();

        usort($results, function (Grosesse $a, Grosesse $b) {
            $priorityMap = ['aRisque' => 1, 'enCours' => 2, 'terminee' => 3];
            $pa = $priorityMap[$a->getStatutGrossesse()] ?? 3;
            $pb = $priorityMap[$b->getStatutGrossesse()] ?? 3;

            // 1. Trier par statut
            if ($pa !== $pb) {
                return $pa <=> $pb;
            }

            // 2. Pour les enCours : trimestre DESC puis semaine DESC
            if ($a->getStatutGrossesse() === 'enCours' && $b->getStatutGrossesse() === 'enCours') {
                $trimestreA = $a->getTrimestreActuel() ?? 0;
                $trimestreB = $b->getTrimestreActuel() ?? 0;

                if ($trimestreA !== $trimestreB) {
                    return $trimestreB <=> $trimestreA;
                }

                $semaineA = $a->getSemaineActuelle() ?? 0;
                $semaineB = $b->getSemaineActuelle() ?? 0;
                return $semaineB <=> $semaineA;
            }

            return 0;
        });

        return $results;
    }

    /**
     * Statistiques par statut pour dashboard admin.
     *
     * @return array<string,int>
     */
    public function getStatsByStatut(): array
    {
        $qb = $this->createQueryBuilder('g')
            ->select('g.statutGrossesse AS statut, COUNT(g.id) AS total')
            ->groupBy('g.statutGrossesse');

        $rows = $qb->getQuery()->getScalarResult();

        $result = [
            'enCours'  => 0,
            'aRisque'  => 0,
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
}