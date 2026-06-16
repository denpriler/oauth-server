<?php

declare(strict_types=1);

namespace App\OAuth\Domain\Event;

use DateTimeImmutable;
use Symfony\Component\Uid\UuidV7;

final readonly class OAuthClientCreated
{
    /**
     * Only ID (no need for more r.n.)
     *
     * @param UuidV7 $id
     * @param DateTimeImmutable $occurredAt
     */
    public function __construct(
        public UuidV7 $id,
        public DateTimeImmutable $occurredAt = new DateTimeImmutable()
    ) {
    }
}
