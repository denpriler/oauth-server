<?php

declare(strict_types=1);

namespace App\Identity\Domain\ValueObject;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Embeddable]
class HashedPassword
{
    #[ORM\Column(type: 'string', length: 255)]
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
}
