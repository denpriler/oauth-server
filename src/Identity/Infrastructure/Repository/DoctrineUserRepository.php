<?php

declare(strict_types=1);

namespace App\Identity\Infrastructure\Repository;

use App\Identity\Domain\Entity\User;
use App\Identity\Domain\Repository\UserRepositoryInterface;
use App\Identity\Domain\ValueObject\Email;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Exception\ORMException;
use Doctrine\ORM\OptimisticLockException;
use Symfony\Component\Uid\UuidV7;

final readonly class DoctrineUserRepository implements UserRepositoryInterface
{
    public function __construct(
        private EntityManagerInterface $em,
    ) {
    }

    // region Query
    /**
     * @throws OptimisticLockException
     * @throws ORMException
     */
    public function findById(UuidV7 $id): ?User
    {
        return $this->em->find(User::class, $id);
    }

    public function findByEmail(Email $email): ?User
    {
        return $this->em->getRepository(User::class)->findOneBy(['email.value' => $email->getValue()]);
    }
    // endregion

    // region Boolean
    public function existsByEmail(Email $email): bool
    {
        return $this->em->createQueryBuilder()
                ->select('1')
                ->from(User::class, 'u')
                ->where('u.email.value = :email')
                ->setParameter('email', $email->getValue())
                ->setMaxResults(1)
                ->getQuery()
                ->getOneOrNullResult() !== null;
    }
    // endregion

    // region Insert or Update
    public function save(User $user): void
    {
        $this->em->persist($user);
        $this->em->flush();
    }
    // endregion
}
