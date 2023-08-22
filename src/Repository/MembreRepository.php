<?php

namespace App\Repository;

use App\Entity\Centre;
use App\Entity\Membre;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\Persistence\ManagerRegistry;

/** @extends ServiceEntityRepository<Membre> */
class MembreRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Membre::class);
    }

    /**
     * @throws NonUniqueResultException
     */
    public function findByDistantId(null|int|string $distantId, string $clientIdentifier): ?Membre
    {
        if (!$distantId) {
            return null;
        }

        return $this->createQueryBuilder('m')
            ->join('m.externalLinks', 'c')
            ->join('c.client', 'client')
            ->andWhere('c.distantId = :distantId')
            ->andWhere('client.randomId = :clientId')
            ->setParameters([
                'distantId' => $distantId,
                'clientId' => $clientIdentifier,
            ])->getQuery()->getOneOrNullResult();
    }

    public function countKPI()
    {
        try {
            return $this->createQueryBuilder('m')
                ->select('count(m.id)')
                ->join('m.user', 'user')
                ->where('user.test = false')
                ->getQuery()->getSingleScalarResult();
        } catch (\Exception $e) {
            return 0;
        }
    }

    public function findByClientIdentifier(string $clientIdentifier)
    {
        return $this->createQueryBuilder('m')
            ->join('m.externalLinks', 'c')
            ->join('c.client', 'client')
            ->andWhere('client.randomId = :clientId')
            ->setParameters(['clientId' => $clientIdentifier])
            ->getQuery()
            ->getResult();
    }

    /**
     * @return Membre[]
     */
    public function findByAuthorizedProfessional(Membre $professional, string $search = null, Centre $relay = null): array
    {
        $qb = $this->createQueryBuilder('m')
            ->innerJoin('m.membresCentres', 'bc')
            ->innerJoin('bc.centre', 'c')
            ->innerJoin('m.user', 'u')
            ->andWhere('bc.bValid = true')
            ->andWhere('m != :professional')
            ->orderBy('u.username')
            ->setParameter('professional', $professional);

        if ($relay) {
            $qb->andWhere('c = :relay')
                ->setParameter('relay', $relay);
        } else {
            $qb->andWhere('c IN (:relays)')
                ->setParameter('relays', $professional->getAffiliatedRelaysWithProfessionalManagement()->toArray());
        }

        if ($search) {
            $qb->andWhere('u.username LIKE :search')
                ->setParameter('search', sprintf('%%%s%%', $search));
        }

        return $qb->getQuery()->getResult();
    }

    /** @return Membre[] */
    public function search(User $loggedUser, ?string $search = ''): array
    {
        $qb = $this->createQueryBuilder('m')
            ->join('m.user', 'u');

        if ($search) {
            foreach (explode(' ', $search) as $word) {
                $qb->andWhere('u.prenom LIKE :search OR u.nom LIKE :search OR u.email LIKE :search')
                    ->andWhere('u != :user')
                    ->setParameters([
                        'user' => $loggedUser,
                        'search' => sprintf('%%%s%%', $word),
                    ]);
            }
        }

        return $qb->getQuery()->getResult();
    }
}
