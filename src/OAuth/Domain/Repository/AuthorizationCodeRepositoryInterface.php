<?php

namespace App\OAuth\Domain\Repository;

use App\OAuth\Domain\Entity\AuthorizationCode;
use Symfony\Component\Uid\UuidV7;

interface AuthorizationCodeRepositoryInterface
{
    // region Query
    public function findById(UuidV7 $id): ?AuthorizationCode;
    // endregion

    // region Insert or Update
    public function save(AuthorizationCode $authorizationCode): void;
    // endregion
}
