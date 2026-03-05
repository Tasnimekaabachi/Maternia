<?php

namespace App\Repository;

use App\Entity\Requirement;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Requirement>
 *
 * @method Requirement|null find($id, $lockMode = null, $lockVersion = null)
 * @method Requirement|null findOneBy(array<string, mixed> $criteria, array<string, string>|null $orderBy = null)
 * @method Requirement[]    findAll()
 * @method Requirement[]    findBy(array<string, mixed> $criteria, array<string, string>|null $orderBy = null, $limit = null, $offset = null)
 */
class RequirementRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Requirement::class);
    }
}