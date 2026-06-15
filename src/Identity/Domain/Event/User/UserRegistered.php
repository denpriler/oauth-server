<?php

declare(strict_types=1);

namespace App\Identity\Domain\Event\User;

use App\Identity\Domain\ValueObject\Email;
use DateTimeImmutable;
use Symfony\Component\Uid\UuidV7;

final readonly class UserRegistered
{
    public function __construct(
        public UuidV7            $userId,
        public Email             $email,
        public DateTimeImmutable $occurredAt = new DateTimeImmutable()
    ) {
    }
}
