<?php

declare(strict_types=1);

namespace App\Identity\Domain\Repository;

use App\Identity\Domain\Entity\User;
use App\Identity\Domain\ValueObject\Email;
use Symfony\Component\Uid\UuidV7;

interface UserRepositoryInterface
{
    // region Query
    public function findById(UuidV7 $id): ?User;
    public function findByEmail(Email $email): ?User;
    // endregion

    // region Boolean
    public function existsByEmail(Email $email): bool;
    // endregion

    // region Insert or Update
    public function save(User $user): void;
    // endregion
}
