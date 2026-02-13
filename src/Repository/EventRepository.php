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
        string $sortOrder = 'DESC'
    ): array {
        $qb = $this->createQueryBuilder('e')
            ->leftJoin('e.eventCat', 'cat');

        if ($searchTerm && trim($searchTerm) !== '') {
            $qb->where('e.title LIKE :search OR e.location LIKE :search OR cat.name LIKE :search')
                ->setParameter('search', '%' . $searchTerm . '%');
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
