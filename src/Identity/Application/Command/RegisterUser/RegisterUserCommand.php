<?php

declare(strict_types=1);

namespace App\Identity\Application\Command\RegisterUser;

use App\Shared\Infrastructure\Http\RequestData\RequestDataInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints as Assert;

final readonly class RegisterUserCommand implements RequestDataInterface
{
    public function __construct(
        #[Assert\NotBlank]
        #[Assert\Email]
        public string $email,
        #[Assert\NotBlank]
        #[Assert\Length(min: 8)]
        #[Assert\PasswordStrength]
        public string $password,
    ) {
    }

    public static function fromRequest(Request $request): self
    {
        $data = $request->toArray();

        return new self(
            email: $data['email'] ?? '',
            password: $data['password'] ?? '',
        );
    }
}
