<?php

declare(strict_types=1);

namespace App\OAuth\Application\Command\CreateOAuthClient;

use App\Shared\Infrastructure\Http\RequestData\RequestDataInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Uid\UuidV7;
use Symfony\Component\Validator\Constraints as Assert;

final class CreateOAuthClientCommand implements RequestDataInterface
{
    /** @var array<string> */
    private const array GRANT_TYPES = [
        'authorization_code',
        'client_credentials',
        'refresh_token',
    ];

    public function __construct(
        #[Assert\NotBlank]
        #[Assert\Type('string')]
        #[Assert\Length(min: 3, max: 255)]
        public readonly string $name,
        #[Assert\NotBlank]
        #[Assert\Type('array')]
        #[Assert\All([new Assert\Url()])]
        public readonly array $redirectUris,
        #[Assert\NotBlank]
        #[Assert\Type('array')]
        #[Assert\Choice(choices: self::GRANT_TYPES, multiple: true)]
        public readonly array $grantTypes,
        #[Assert\Type('boolean')]
        public readonly bool $isConfidential,
        public ?UuidV7 $ownerId = null,
    ) {
    }

    public static function fromRequest(Request $request): self
    {
        $data = $request->toArray();

        return new self(
            name: $data['name'] ?? '',
            redirectUris: $data['redirect_uris'] ?? [],
            grantTypes: $data['grant_types'] ?? [],
            isConfidential: $data['is_confidential'] ?? true,
        );
    }

    public function setOwnerId(UuidV7 $ownerId): self
    {
        $this->ownerId = $ownerId;
        return $this;
    }
}
