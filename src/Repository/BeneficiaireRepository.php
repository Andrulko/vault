<?php

namespace App\Repository;

use App\Entity\Beneficiaire;
use App\Entity\BeneficiaireCentre;
use App\Entity\Client;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Beneficiaire|null find($id, $lockMode = null, $lockVersion = null)
 * @method Beneficiaire|null findOneBy(array $criteria, array $orderBy = null)
 * @method Beneficiaire[]    findAll()
 * @method Beneficiaire[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class BeneficiaireRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Beneficiaire::class);
    }

    /**
     * Retourne le Beneficiaire qui sont liés à un centre et en cours de création
     * c'est à dire que la création n'est pas allé jusqu'au bout.
     */
    public function findByIsCreatingJoinCentre(): array
    {
        $qb = $this->createQueryBuilder('b');
        $isCreating = true;

        $qb
            ->join(BeneficiaireCentre::class, 'bc')
            ->where('b.isCreating = :isCreating')
            ->setParameter('isCreating', $isCreating);

        return $qb->getQuery()->getResult();
    }

    public function findRosalies(): array
    {
        $qb = $this->createQueryBuilder('b');

        $qb
            ->where('b.idRosalie IS NOT NULL');

        return $qb->getQuery()->getResult();
    }

    public function findByDistantId(int|string $distantId, string $clientIdentifier): ?Beneficiaire
    {
        try {
            return $this->createQueryBuilder('b')
                ->join('b.externalLinks', 'c')
                ->join('c.client', 'client')
                ->andWhere('(c.distantId = :distantId AND client.randomId = :clientId) OR b.siSiaoNumber = :distantId')
                ->setParameters([
                    'distantId' => $distantId,
                    'clientId' => $clientIdentifier,
                ])->getQuery()->getOneOrNullResult();
        } catch (NonUniqueResultException) {
            return null;
        }
    }

    public function findByDistantIds(array $distantIds, string $clientIdentifier): array
    {
        return $this->createQueryBuilder('b')
            ->join('b.externalLinks', 'c')
            ->join('c.client', 'client')
            ->andWhere('c.distantId IN (:distantIds)')
            ->andWhere('client.randomId = :clientId')
            ->setParameters([
                'distantIds' => $distantIds,
                'clientId' => $clientIdentifier,
            ])->getQuery()->getResult();
    }

    public function findByUsername(string $username): ?Beneficiaire
    {
        return $this->createQueryBuilder('b')
            ->join('b.user', 'user')
            ->where('user.username = :username')
            ->setParameter('username', $username)
            ->getQuery()->getOneOrNullResult();
    }

    public function countKPI()
    {
        $qb = $this->createQueryBuilder('b');

        $qb->select('count(b.id)')
            ->join('b.user', 'user')
            ->where('isCreating = false')
            ->where('user.test = false');

        try {
            return $qb->getQuery()->getSingleScalarResult();
        } catch (\Exception $e) {
            return 0;
        }
    }

    public function getEntitiesAxel()
    {
        return $this->createQueryBuilder('b')
            ->join('b.externalLinks', 'c')
            ->andWhere('c.client = :clientId')
            ->setParameter('clientId', 6)
            ->getQuery()->getResult();
    }

    public function findByClientIdentifier(string $clientIdentifier): array
    {
        return $this->createQueryBuilder('b')
            ->join('b.externalLinks', 'c')
            ->join('c.client', 'client')
            ->andWhere('client.randomId = :clientId')
            ->setParameters(['clientId' => $clientIdentifier])
            ->getQuery()
            ->getResult();
    }

    public function getBeneficiariesSiSiaoNumbers(?Client $client): array
    {
//        return $this->createQueryBuilder('b')
//            ->select('b.siSiaoNumber')
//            ->andWhere('b.siSiaoNumber IS NOT NULL')
//            ->getQuery()
//            ->getArrayResult();
        return $this->createQueryBuilder('b')
            ->select('c.distantId')
            ->join('b.externalLinks', 'c')
            ->join('c.client', 'client')
            ->andWhere('client.randomId = :clientId')
            ->andWhere('c.distantId IS NOT NULL')
            ->setParameters(['clientId' => $client->getRandomId()])
            ->getQuery()
            ->getArrayResult();
    }

    /**
     * @return Beneficiaire[]
     */
    public function searchByUsernameInformation(?string $firstname, ?string $lastname, ?\DateTime $birthDate): array
    {
        $parameters = [];
        $qb = $this->createQueryBuilder('b')
            ->innerJoin('b.user', 'u')
            ->where('b.isCreating = false');

        if ($firstname) {
            $qb->andWhere('u.prenom LIKE :firstname');
            $parameters['firstname'] = sprintf('%%%s%%', $firstname);
        }

        if ($lastname) {
            $qb->andWhere('u.nom LIKE :lastname');
            $parameters['lastname'] = sprintf('%%%s%%', $lastname);
        }

        if ($birthDate) {
            $qb->andWhere('b.dateNaissance = :birthDate');
            $parameters['birthDate'] = $birthDate;
        }
        $qb->setParameters($parameters)
            ->orderBy('u.nom');

        return $qb->getQuery()->getResult();
    }
}
