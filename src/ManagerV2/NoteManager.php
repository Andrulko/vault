<?php

namespace App\ManagerV2;

use App\Entity\Beneficiaire;
use App\Entity\Note;
use App\Repository\NoteRepository;
use App\ServiceV2\Traits\UserAwareTrait;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Security;

class NoteManager
{
    use UserAwareTrait;

    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly NoteRepository $repository,
        private Security $security,
    ) {
    }

    /**
     * @return Note[]
     */
    public function getNotes(Beneficiaire $beneficiary, string $search = null): array
    {
        return $this->repository->findByBeneficiary(
            $beneficiary,
            $this->getUser() === $beneficiary->getUser(),
            $search,
        );
    }

    public function toggleVisibility(Note $note): void
    {
        $note->toggleVisibility();
        $this->em->flush();
    }
}
