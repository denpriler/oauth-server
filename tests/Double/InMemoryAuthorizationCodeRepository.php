<?php

declare(strict_types=1);

namespace App\Tests\Double;

use App\OAuth\Domain\Entity\AuthorizationCode;
use App\OAuth\Domain\Repository\AuthorizationCodeRepositoryInterface;
use Symfony\Component\Uid\UuidV7;

/**
 * In-memory test double for AuthorizationCodeRepositoryInterface.
 * Uses static storage so data survives kernel reboots inside a single test.
 * Call reset() in setUp() to prevent state leaking between tests.
 */
final class InMemoryAuthorizationCodeRepository implements AuthorizationCodeRepositoryInterface
{
    /** @var array<string, AuthorizationCode> */
    private static array $codes = [];

    public static function reset(): void
    {
        self::$codes = [];
    }

    public function save(AuthorizationCode $authorizationCode): void
    {
        self::$codes[(string) $authorizationCode->getId()] = $authorizationCode;
    }

    public function findById(UuidV7 $id): ?AuthorizationCode
    {
        return self::$codes[(string) $id] ?? null;
    }

    public function findByCode(string $code): ?AuthorizationCode
    {
        foreach (self::$codes as $authorizationCode) {
            if ($authorizationCode->getCode() === $code) {
                return $authorizationCode;
            }
        }

        return null;
    }
}
