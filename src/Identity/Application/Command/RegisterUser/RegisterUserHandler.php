<?php

declare(strict_types=1);

namespace App\Identity\Application\Command\RegisterUser;

use App\Identity\Domain\Entity\User;
use App\Identity\Domain\Repository\UserRepositoryInterface;
use App\Identity\Domain\ValueObject\Email;
use App\Identity\Domain\ValueObject\HashedPassword;
use DomainException;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[AsMessageHandler]
final readonly class RegisterUserHandler
{
    public function __construct(
        private UserRepositoryInterface $userRepository,
        private UserPasswordHasherInterface $hasher
    ) {
    }

    public function __invoke(RegisterUserCommand $command): void
    {
        $email = new Email($command->email);

        if ($this->userRepository->existsByEmail($email)) {
            throw new DomainException("User with email {$email} already exists");
        }

        $user = User::register($email);
        $plainPasswordHash = $this->hasher->hashPassword($user, $command->password);
        $user->setPassword(HashedPassword::fromHash($plainPasswordHash));

        $this->userRepository->save($user);
    }
}
