<?php

declare(strict_types=1);

namespace App\OAuth\Domain\ValueObject;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Embeddable]
class ClientSecretHash
{
    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private string $value;

    private function __construct(string $hash)
    {
        $this->value = $hash;
    }

    public static function fromHash(string $hash): self
    {
        return new self($hash);
    }

    public function getValue(): string
    {
        return $this->value;
    }

    public function verify(string $plain): bool
    {
        return password_verify($plain, $this->value);
    }

    public function equals(self $other): bool
    {
        return $this->value === $other->value;
    }

    public function __toString(): string
    {
        return $this->value;
    }
}
