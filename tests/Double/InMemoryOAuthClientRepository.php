<?php

declare(strict_types=1);

namespace App\Tests\Double;

use App\OAuth\Domain\Entity\OAuthClient;
use App\OAuth\Domain\Repository\OAuthClientRepositoryInterface;
use Symfony\Component\Uid\UuidV7;

/**
 * In-memory test double for OAuthClientRepositoryInterface.
 * Uses static storage so data survives kernel reboots inside a single test.
 * Call reset() in setUp() to prevent state leaking between tests.
 */
final class InMemoryOAuthClientRepository implements OAuthClientRepositoryInterface
{
    /** @var array<string, OAuthClient> */
    private static array $clients = [];

    public static function reset(): void
    {
        self::$clients = [];
    }

    public function save(OAuthClient $client): void
    {
        self::$clients[(string) $client->getId()] = $client;
    }

    public function findById(UuidV7 $id): ?OAuthClient
    {
        return self::$clients[(string) $id] ?? null;
    }
}
