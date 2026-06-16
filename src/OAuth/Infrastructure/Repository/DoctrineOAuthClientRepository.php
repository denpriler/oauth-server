<?php

namespace App\OAuth\Infrastructure\Repository;

use App\OAuth\Domain\Entity\OAuthClient;
use App\OAuth\Domain\Repository\OAuthClientRepositoryInterface;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Exception\ORMException;
use Doctrine\ORM\OptimisticLockException;
use Symfony\Component\Uid\UuidV7;

final readonly class DoctrineOAuthClientRepository implements OAuthClientRepositoryInterface
{
    public function __construct(
        private EntityManagerInterface $em,
    ) {
    }

    /**
     * @throws OptimisticLockException
     * @throws ORMException
     */
    public function findById(UuidV7 $id): ?OAuthClient
    {
        return $this->em->find(OAuthClient::class, $id);
    }

    public function save(OAuthClient $client): void
    {
        $this->em->persist($client);
        $this->em->flush();
    }
}
