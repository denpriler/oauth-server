<?php

declare(strict_types=1);

namespace App\Identity\Application\Command\LoginUser;

use App\Shared\Infrastructure\Http\RequestData\RequestDataInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints as Assert;

final readonly class LoginUserCommand implements RequestDataInterface
{
    public function __construct(
        #[Assert\NotBlank]
        #[Assert\Email]
        public string $email,
        #[Assert\NotBlank]
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
