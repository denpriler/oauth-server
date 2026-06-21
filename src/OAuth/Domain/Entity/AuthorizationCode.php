<?php

declare(strict_types=1);

namespace App\OAuth\Domain\Entity;

use App\OAuth\Domain\Enum\AuthorizationCode\Scope;
use App\OAuth\Domain\Event\AuthorizationCode\AuthorizationCodeCreated;
use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Uid\UuidV7;

#[ORM\Entity]
#[ORM\Table(name: 'authorization_codes')]
class AuthorizationCode
{
    #[ORM\Id]
    #[ORM\Column(type: 'uuid')]
    private UuidV7 $id;

    #[ORM\Column(name: 'client_id', type: 'uuid')]
    private UuidV7 $clientId;

    #[ORM\Column(name: 'user_id', type: 'uuid')]
    private UuidV7 $userId;

    #[ORM\Column(name: 'redirect_uri', type: 'string')]
    private string $redirectUri;

    /** @var array<string> */
    #[ORM\Column(name: 'scopes', type: '_text')]
    private array $scopes = [];

    #[ORM\Column(name: 'code', type: 'string', unique: true)]
    private string $code;

    #[ORM\Column(name: 'code_challenge', type: 'string')]
    private string $codeChallenge;

    #[ORM\Column(name: 'code_challenge_method', type: 'string')]
    private string $codeChallengeMethod;

    #[ORM\Column(name: 'state', type: 'string', nullable: true)]
    private ?string $state;

    #[ORM\Column(name: 'expired_at', type: 'datetimetz_immutable')]
    private DateTimeImmutable $expiredAt;

    #[ORM\Column(name: 'used', type: 'boolean')]
    private bool $used = false;

    private array $domainEvents = [];

    // region Constructor
    private function __construct(
        UuidV7 $id,
        UuidV7 $clientId,
        UuidV7 $userId,
        string $redirectUri,
        string $code,
        string $codeChallenge,
        string $codeChallengeMethod,
        DateTimeImmutable $expiredAt,
        array $scopes = [],
        ?string $state = null,
    ) {
        $this->id = $id;
        $this->clientId = $clientId;
        $this->userId = $userId;
        $this->redirectUri = $redirectUri;
        $this->scopes = $scopes;
        $this->code = $code;
        $this->codeChallenge = $codeChallenge;
        $this->codeChallengeMethod = $codeChallengeMethod;
        $this->expiredAt = $expiredAt;
        $this->state = $state;
    }

    public static function create(
        UuidV7 $clientId,
        UuidV7 $userId,
        string $redirectUri,
        string $code,
        string $codeChallenge,
        string $codeChallengeMethod,
        array $scopes = [],
        ?string $state = null,
    ): self {
        $scopes = array_values(array_filter(array_map(
            fn (string|Scope $s) => $s instanceof Scope ? $s->value : Scope::tryFrom($s)?->value,
            $scopes,
        )));

        $authCode = new self(
            id: new UuidV7(),
            clientId: $clientId,
            userId: $userId,
            redirectUri: $redirectUri,
            code: $code,
            codeChallenge: $codeChallenge,
            codeChallengeMethod: $codeChallengeMethod,
            expiredAt: new DateTimeImmutable('+10 minutes'),
            scopes: $scopes,
            state: $state,
        );
        $authCode->domainEvents[] = new AuthorizationCodeCreated($authCode->id);

        return $authCode;
    }
    // endregion

    // region Getters
    public function getId(): UuidV7
    {
        return $this->id;
    }

    public function getClientId(): UuidV7
    {
        return $this->clientId;
    }

    public function getUserId(): UuidV7
    {
        return $this->userId;
    }

    public function getRedirectUri(): string
    {
        return $this->redirectUri;
    }

    public function getCode(): string
    {
        return $this->code;
    }

    public function getCodeChallenge(): string
    {
        return $this->codeChallenge;
    }

    public function getCodeChallengeMethod(): string
    {
        return $this->codeChallengeMethod;
    }

    public function getState(): ?string
    {
        return $this->state;
    }

    public function getExpiredAt(): DateTimeImmutable
    {
        return $this->expiredAt;
    }

    public function isUsed(): bool
    {
        return $this->used;
    }

    /** @return array<Scope> */
    public function getScopes(): array
    {
        return array_map(fn (string $scope) => Scope::from($scope), $this->scopes);
    }

    public function pullDomainEvents(): array
    {
        $events = $this->domainEvents;
        $this->domainEvents = [];

        return $events;
    }
    // endregion
}
