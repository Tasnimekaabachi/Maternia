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
    public function search($ville, $tarif)
{
    $qb = $this->createQueryBuilder('o');

    if($ville){
        $qb->andWhere('o.ville LIKE :ville')
           ->setParameter('ville','%'.$ville.'%');
    }

    if($tarif){
        $qb->andWhere('o.tarif <= :tarif')
           ->setParameter('tarif',$tarif);
    }

    return $qb->getQuery()->getResult();
}

    //    /**
    //     * @return OffreBabySitter[] Returns an array of OffreBabySitter objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('o')
    //            ->andWhere('o.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('o.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?OffreBabySitter
    //    {
    //        return $this->createQueryBuilder('o')
    //            ->andWhere('o.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
