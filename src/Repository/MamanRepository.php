<?php

namespace App\Repository;

use App\Entity\Maman;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Maman>
 */
class MamanRepository extends ServiceEntityRepository
{
    public const SORT_TAILLE = 'taille';
    public const SORT_POIDS = 'poids';
    public const SORT_DATE = 'dateCreation';

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Maman::class);
    }

    /**
     * Liste pour l'admin avec filtre groupe sanguin et tri (taille, poids, date).
     *
     * @return Maman[]
     */
    public function findForAdmin(?string $groupeSanguin = null, ?string $sortBy = null, string $sortOrder = 'DESC'): array
    {
        $orderField = \in_array($sortBy, [self::SORT_TAILLE, self::SORT_POIDS, self::SORT_DATE], true)
            ? $sortBy
            : self::SORT_DATE;
        $qb = $this->createQueryBuilder('m')
            ->orderBy('m.' . $orderField, $sortOrder === 'ASC' ? 'ASC' : 'DESC');
        if ($groupeSanguin !== null && $groupeSanguin !== '') {
            $qb->andWhere('m.groupeSanguin = :groupe')
                ->setParameter('groupe', $groupeSanguin);
        }
        return $qb->getQuery()->getResult();
    }

    /**
     * Statistiques par groupe sanguin (effectif par groupe).
     *
     * @return array<string, int>
     */
    public function getStatsByGroupeSanguin(): array
    {
        $qb = $this->createQueryBuilder('m')
            ->select('m.groupeSanguin as groupe', 'COUNT(m.id) as nb')
            ->groupBy('m.groupeSanguin')
            ->orderBy('nb', 'DESC');
        $rows = $qb->getQuery()->getResult();
        $stats = [];
        foreach ($rows as $row) {
            $stats[(string) $row['groupe']] = (int) $row['nb'];
        }
        return $stats;
    }

    /**
     * Statistiques par mode de vie (fumeur): Oui / Non.
     *
     * @return array{oui: int, non: int}
     */
    public function getStatsByFumeur(): array
    {
        $qbOui = $this->createQueryBuilder('m')->select('COUNT(m.id)')->where('m.fumeur = true');
        $qbNon = $this->createQueryBuilder('m')->select('COUNT(m.id)')->where('m.fumeur = false');
        return [
            'oui' => (int) $qbOui->getQuery()->getSingleScalarResult(),
            'non' => (int) $qbNon->getQuery()->getSingleScalarResult(),
        ];
    }
}
