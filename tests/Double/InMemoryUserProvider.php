<?php

declare(strict_types=1);

namespace App\Tests\Double;

use App\Identity\Domain\Entity\User;
use App\Identity\Domain\ValueObject\Email;
use Symfony\Component\Security\Core\Exception\UserNotFoundException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

/**
 * In-memory user provider for tests.
 * Delegates to InMemoryUserRepository so stateless-JWT firewall can reload
 * the authenticated user without hitting the database.
 */
final class InMemoryUserProvider implements UserProviderInterface
{
    public function loadUserByIdentifier(string $identifier): UserInterface
    {
        $user = InMemoryUserRepository::findByEmailStatic(new Email($identifier));

        if ($user === null) {
            $e = new UserNotFoundException();
            $e->setUserIdentifier($identifier);
            throw $e;
        }

        return $user;
    }

    public function refreshUser(UserInterface $user): UserInterface
    {
        return $this->loadUserByIdentifier($user->getUserIdentifier());
    }

    public function supportsClass(string $class): bool
    {
        return $class === User::class || is_subclass_of($class, User::class);
    }
}
