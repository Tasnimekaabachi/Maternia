<?php

namespace App\Repository;

use App\Entity\OffreBabySitter;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<OffreBabySitter>
 */
class OffreBabySitterRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, OffreBabySitter::class);
    }

    /**
     * Recherche avancée par ville, tarif maximum et tri.
     *
     * @param string|null $ville       Ville (contient)
     * @param float|null  $tarifMax    Tarif maximum
     * @param string|null $sort        Clé de tri (tarif_asc, tarif_desc, experience_asc, experience_desc)
     *
     * @return OffreBabySitter[]
     */
    public function search(?string $ville, ?float $tarifMax, ?string $sort): array
    {
        $qb = $this->createQueryBuilder('o');

        if ($ville !== null && $ville !== '') {
            $qb->andWhere('o.ville LIKE :ville')
               ->setParameter('ville', '%' . $ville . '%');
        }

        if ($tarifMax !== null && $tarifMax > 0) {
            $qb->andWhere('o.tarif <= :tarifMax')
               ->setParameter('tarifMax', $tarifMax);
        }

        switch ($sort) {
            case 'tarif_asc':
                $qb->orderBy('o.tarif', 'ASC');
                break;
            case 'tarif_desc':
                $qb->orderBy('o.tarif', 'DESC');
                break;
            case 'experience_asc':
                $qb->orderBy('o.experience', 'ASC');
                break;
            case 'experience_desc':
                $qb->orderBy('o.experience', 'DESC');
                break;
            default:
                // Pas de tri explicite : on peut trier par ID décroissant (les plus récentes d'abord)
                $qb->orderBy('o.id', 'DESC');
                break;
        }

        return $qb->getQuery()->getResult();
    }

    /**
     * Statistiques d'offres par ville (nombre d'offres et tarif moyen).
     *
     * @return array<int, array{ville: string, nbOffres: int, tarifMoyen: float}>
     */
    public function statsByVille(): array
    {
        $qb = $this->createQueryBuilder('o')
            ->select('o.ville AS ville, COUNT(o.id) AS nbOffres, AVG(o.tarif) AS tarifMoyen')
            ->groupBy('o.ville')
            ->orderBy('nbOffres', 'DESC');

        return $qb->getQuery()->getResult();
    }
}
