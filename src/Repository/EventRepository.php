<?php

namespace App\Repository;

use App\Entity\Event;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;


class EventRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Event::class);
    }

    public function findWithSearchAndSort(
        ?string $searchTerm,
        string $sortBy = 'startAt',
        string $sortOrder = 'DESC',
        ?int $categoryId = null,
        ?string $status = null,
        ?string $organizer = null
    ): array {
        $qb = $this->createQueryBuilder('e')
            ->leftJoin('e.eventCat', 'cat');

        if ($searchTerm && trim($searchTerm) !== '') {
            $qb->andWhere('e.title LIKE :search OR e.location LIKE :search OR cat.name LIKE :search')
                ->setParameter('search', '%' . $searchTerm . '%');
        }

        if ($categoryId) {
            $qb->andWhere('e.eventCat = :categoryId')
                ->setParameter('categoryId', $categoryId);
        }

        if ($organizer) {
            if ($organizer === 'admin') {
                $qb->andWhere('e.creator IS NULL');
            } elseif ($organizer === 'user') {
                $qb->andWhere('e.creator IS NOT NULL');
            }
        }

        if ($status) {
            $now = new \DateTime();
            switch ($status) {
                case 'weekly':
                    $qb->andWhere('e.isWeekly = true');
                    break;
                case 'upcoming':
                    $qb->andWhere('e.isWeekly = false')
                        ->andWhere('e.startAt > :now')
                        ->setParameter('now', $now);
                    break;
                case 'ongoing':
                    $qb->andWhere('e.isWeekly = false')
                        ->andWhere('e.startAt <= :now')
                        ->andWhere('e.endAt >= :now')
                        ->setParameter('now', $now);
                    break;
                case 'past':
                    $qb->andWhere('e.isWeekly = false')
                        ->andWhere('e.endAt < :now')
                        ->setParameter('now', $now);
                    break;
            }
        }

        $allowedSortFields = ['title', 'startAt', 'endAt', 'location'];
        $allowedSortOrders = ['ASC', 'DESC'];

        $sortBy = in_array($sortBy, $allowedSortFields) ? $sortBy : 'startAt';
        $sortOrder = in_array(strtoupper($sortOrder), $allowedSortOrders) ? strtoupper($sortOrder) : 'DESC';

        if ($sortBy === 'category') {
            $qb->orderBy('cat.name', $sortOrder);
        } else {
            $qb->orderBy('e.' . $sortBy, $sortOrder);
        }

        return $qb->getQuery()->getResult();
    }
    public function findUpcomingEvents(?int $limit = null): array
    {
        $qb = $this->createQueryBuilder('e')
            ->orderBy('e.startAt', 'ASC');

        if ($limit) {
            $qb->setMaxResults($limit);
        }

        return $qb->getQuery()->getResult();
    }

    public function findRecents(int $limit = 5): array
    {
        return $this->createQueryBuilder('e')
            ->orderBy('e.id', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }
}
