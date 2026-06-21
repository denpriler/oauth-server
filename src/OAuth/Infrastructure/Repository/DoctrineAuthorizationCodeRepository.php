<?php

namespace App\OAuth\Infrastructure\Repository;

use App\OAuth\Domain\Entity\AuthorizationCode;
use App\OAuth\Domain\Repository\AuthorizationCodeRepositoryInterface;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Exception\ORMException;
use Doctrine\ORM\OptimisticLockException;
use Symfony\Component\Uid\UuidV7;

final readonly class DoctrineAuthorizationCodeRepository implements AuthorizationCodeRepositoryInterface
{
    public function __construct(
        private EntityManagerInterface $em,
    ) {
    }

    /**
     * @throws OptimisticLockException
     * @throws ORMException
     */
    public function findById(UuidV7 $id): ?AuthorizationCode
    {
        return $this->em->find(AuthorizationCode::class, $id);
    }

    public function save(AuthorizationCode $authorizationCode): void
    {
        $this->em->persist($authorizationCode);
        $this->em->flush();
    }
}
