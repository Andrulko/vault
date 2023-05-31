<?php

namespace App\Repository;

use App\Entity\Beneficiaire;
use App\Entity\Evenement;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Evenement|null find($id, $lockMode = null, $lockVersion = null)
 * @method Evenement|null findOneBy(array $criteria, array $orderBy = null)
 * @method Evenement[]    findAll()
 * @method Evenement[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class EvenementRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Evenement::class);
    }

    /**
     * @return Evenement[]
     */
    public function findFutureEventsByBeneficiary(Beneficiaire $beneficiary, bool $isOwner, string $search = null): array
    {
        $qb = $this->createQueryBuilder('e')
            ->leftJoin('e.rappels', 'r')
            ->andWhere('e.beneficiaire = :beneficiary')
            ->andWhere('e.date > :date')
            ->orderBy('e.date', 'ASC');

        $parameters = [
            'beneficiary' => $beneficiary,
            'date' => new \DateTime(),
        ];

        if ($search) {
            $qb->andWhere('e.nom LIKE :search OR e.commentaire LIKE :search OR e.date LIKE :search');
            $parameters['search'] = sprintf('%%%s%%', $search);
        }

        if (!$isOwner) {
            $qb->andWhere('e.bPrive = FALSE');
        }

        return $qb->setParameters($parameters)
            ->getQuery()
            ->getResult();
    }
}
