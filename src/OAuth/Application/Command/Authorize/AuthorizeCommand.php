<?php

declare(strict_types=1);

namespace App\OAuth\Application\Command\Authorize;

use App\Shared\Infrastructure\Http\RequestData\RequestDataInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Uid\UuidV7;
use Symfony\Component\Validator\Constraints as Assert;

final class AuthorizeCommand implements RequestDataInterface
{
    /** @var array<string> */
    private const array SCOPES = [
        'email',
        'profile',
    ];

    public function __construct(
        #[Assert\NotBlank]
        #[Assert\EqualTo('code')]
        public readonly string $responseType,
        #[Assert\NotBlank]
        #[Assert\Uuid]
        public readonly string $clientId,
        #[Assert\NotBlank]
        #[Assert\Url]
        public readonly string $redirectUri,
        #[Assert\NotBlank]
        public readonly string $codeChallenge,
        #[Assert\NotBlank]
        #[Assert\EqualTo('S256')]
        public readonly string $codeChallengeMethod,
        #[Assert\Type('array')]
        #[Assert\Choice(choices: self::SCOPES, multiple: true)]
        public readonly array $scopes = [],
        public readonly ?string $state = null,
        public ?UuidV7 $userId = null,
    ) {
    }

    public static function fromRequest(Request $request): self
    {
        return new self(
            responseType: $request->query->get('response_type', ''),
            clientId: $request->query->get('client_id', ''),
            redirectUri: $request->query->get('redirect_uri', ''),
            codeChallenge: $request->query->get('code_challenge', ''),
            codeChallengeMethod: $request->query->get('code_challenge_method', ''),
            scopes: array_values(array_filter(explode(' ', $request->query->get('scope', '')))),
            state: $request->query->get('state'),
        );
    }

    public function setUserId(UuidV7 $userId): self
    {
        $this->userId = $userId;

        return $this;
    }
}
