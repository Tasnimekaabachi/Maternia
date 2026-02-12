<?php

namespace App\Repository;

use App\Entity\EventCat;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;


class EventCatRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, EventCat::class);
    }
    public function findWithSearchAndSort(
        ?string $searchTerm, 
        string $sortBy = 'name', 
        string $sortOrder = 'ASC'
    ): array {
        $qb = $this->createQueryBuilder('ec');
        if ($searchTerm && trim($searchTerm) !== '') {
            $qb->where('ec.name LIKE :search OR ec.description LIKE :search')
            ->setParameter('search', '%' . $searchTerm . '%');
        }
        $allowedSortFields = ['name', 'eventCount'];
        $allowedSortOrders = ['ASC', 'DESC'];
    
        $sortBy = in_array($sortBy, $allowedSortFields) ? $sortBy : 'name';
        $sortOrder = in_array(strtoupper($sortOrder), $allowedSortOrders) ? strtoupper($sortOrder) : 'ASC';
        if ($sortBy === 'eventCount') {
            $qb->leftJoin('ec.events', 'e')
            ->groupBy('ec.id')
            ->orderBy('COUNT(e.id)', $sortOrder);
        } else {
            $qb->orderBy('ec.' . $sortBy, $sortOrder);
        }
    
        return $qb->getQuery()->getResult();
    }
}
