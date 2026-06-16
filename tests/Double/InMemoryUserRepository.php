<?php

declare(strict_types=1);

namespace App\Tests\Double;

use App\Identity\Domain\Entity\User;
use App\Identity\Domain\Repository\UserRepositoryInterface;
use App\Identity\Domain\ValueObject\Email;
use Symfony\Component\Uid\UuidV7;

/**
 * In-memory test double for UserRepositoryInterface.
 * Uses static storage so data survives kernel reboots inside a single test.
 * Call reset() in setUp() to prevent state leaking between tests.
 */
final class InMemoryUserRepository implements UserRepositoryInterface
{
    /** @var array<string, User> */
    private static array $users = [];

    public static function reset(): void
    {
        self::$users = [];
    }

    public function save(User $user): void
    {
        self::$users[(string) $user->getId()] = $user;
    }

    public function findById(UuidV7 $id): ?User
    {
        return self::$users[(string) $id] ?? null;
    }

    public function findByEmail(Email $email): ?User
    {
        return self::findByEmailStatic($email);
    }

    public static function findByEmailStatic(Email $email): ?User
    {
        foreach (self::$users as $user) {
            if ($user->getEmail()->equals($email)) {
                return $user;
            }
        }

        return null;
    }

    public function existsByEmail(Email $email): bool
    {
        return $this->findByEmail($email) !== null;
    }
}
