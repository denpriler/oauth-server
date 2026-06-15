<?php

declare(strict_types=1);

namespace App\Identity\Application\Command\LoginUser;

use App\Identity\Domain\Repository\UserRepositoryInterface;
use App\Identity\Domain\ValueObject\Email;
use DomainException;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[AsMessageHandler]
final readonly class LoginUserHandler
{
    public function __construct(
        private UserRepositoryInterface $userRepository,
        private UserPasswordHasherInterface $hasher,
        private JWTTokenManagerInterface $tokenManager
    ) {
    }

    public function __invoke(LoginUserCommand $command): string
    {
        $user = $this->userRepository->findByEmail(new Email($command->email))
            ?? throw new DomainException('Invalid credentials');

        if (!$this->hasher->isPasswordValid($user, $command->password)) {
            throw new DomainException('Invalid credentials');
        }

        return $this->tokenManager->create($user);
    }
}
