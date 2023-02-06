<?php

namespace App\Repository;

use App\Entity\Annotations\ClientPermission;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method ClientPermission|null find($id, $lockMode = null, $lockVersion = null)
 * @method ClientPermission|null findOneBy(array $criteria, array $orderBy = null)
 * @method ClientPermission[]    findAll()
 * @method ClientPermission[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ClientPermissionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ClientPermission::class);
    }

    // /**
    //  * @return ClientPermission[] Returns an array of ClientPermission objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('c.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?ClientPermission
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
