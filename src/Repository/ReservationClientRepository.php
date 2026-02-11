<?php

namespace App\Repository;

use App\Entity\ReservationClient;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ReservationClient>
 *
 * @method ReservationClient|null find($id, $lockMode = null, $lockVersion = null)
 * @method ReservationClient|null findOneBy(array $criteria, array $orderBy = null)
 * @method ReservationClient[]    findAll()
 * @method ReservationClient[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ReservationClientRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ReservationClient::class);
    }

    public function save(ReservationClient $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(ReservationClient $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
}
