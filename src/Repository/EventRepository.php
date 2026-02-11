<?php

namespace App\Repository;

use App\Entity\Event;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Event>
 */
class EventRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Event::class);
    }

    // In src/Repository/EventRepository.php
    public function findWithSearchAndSort(
        ?string $searchTerm,
        string $sortBy = 'startAt',
        string $sortOrder = 'DESC'
    ): array {
        $qb = $this->createQueryBuilder('e')
            ->leftJoin('e.eventCat', 'cat');

        // Apply search filter if searchTerm is not empty
        if ($searchTerm && trim($searchTerm) !== '') {
            $qb->where('e.title LIKE :search OR e.location LIKE :search OR cat.name LIKE :search')
                ->setParameter('search', '%' . $searchTerm . '%');
        }

        // Validate and apply sorting
        $allowedSortFields = ['title', 'startAt', 'endAt', 'location'];
        $allowedSortOrders = ['ASC', 'DESC'];

        $sortBy = in_array($sortBy, $allowedSortFields) ? $sortBy : 'startAt';
        $sortOrder = in_array(strtoupper($sortOrder), $allowedSortOrders) ? strtoupper($sortOrder) : 'DESC';

        // Special handling for category sorting
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
            // ->where('e.startAt >= :now')
            // ->setParameter('now', new \DateTime())
            ->orderBy('e.startAt', 'ASC');

        if ($limit) {
            $qb->setMaxResults($limit);
        }

        return $qb->getQuery()->getResult();
    }
}
