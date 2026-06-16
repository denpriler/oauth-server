<?php

declare(strict_types=1);

namespace App\OAuth\Domain\Repository;

use App\OAuth\Domain\Entity\OAuthClient;
use Symfony\Component\Uid\UuidV7;

interface OAuthClientRepositoryInterface
{
    // region Query
    public function findById(UuidV7 $id): ?OAuthClient;
    // endregion

    // region Insert or Update
    public function save(OAuthClient $client): void;
    // endregion
}
