<?php

declare(strict_types=1);

namespace App\Identity\Domain\Entity;

use App\Identity\Domain\Event\User\UserRegistered;
use App\Identity\Domain\ValueObject\Email;
use App\Identity\Domain\ValueObject\HashedPassword;
use DateTimeImmutable;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Uid\UuidV7;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'users')]
class User implements PasswordAuthenticatedUserInterface, UserInterface
{
    #[ORM\Id]
    #[ORM\Column(type: 'uuid')]
    private UuidV7 $id;

    #[ORM\Embedded(class: Email::class)]
    private Email $email;

    #[ORM\Embedded(class: HashedPassword::class)]
    private ?HashedPassword $password = null;

    #[ORM\Column(type: 'boolean')]
    private bool $isActive = true;

    #[ORM\Column(type: 'datetimetz_immutable')]
    private DateTimeImmutable $createdAt;

    private array $domainEvents = [];

    // region Constructor
    private function __construct(
        UuidV7 $id,
        Email $email,
    ) {
        $this->id = $id;
        $this->email = $email;
        $this->createdAt = new DateTimeImmutable();
    }

    public static function register(Email $email): self
    {
        $user = new self(new UuidV7(), $email);
        $user->domainEvents[] = new UserRegistered($user->id, $email);

        return $user;
    }
    // endregion

    // region Getters && Setters
    public function getId(): UuidV7
    {
        return $this->id;
    }
    public function getEmail(): Email
    {
        return $this->email;
    }
    public function isActive(): bool
    {
        return $this->isActive;
    }

    public function pullDomainEvents(): array
    {
        $events = $this->domainEvents;
        $this->domainEvents = [];
        return $events;
    }

    public function getPassword(): ?string
    {
        return $this->password?->getValue();
    }

    public function getRoles(): array
    {
        return ['ROLE_USER'];
    }

    public function getUserIdentifier(): string
    {
        return $this->email->getValue();
    }

    //

    public function setPassword(HashedPassword $password): self
    {
        $this->password = $password;
        return $this;
    }
    // endregion
}
