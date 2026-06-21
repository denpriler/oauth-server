<?php

declare(strict_types=1);

namespace App\OAuth\Domain\Entity;

use App\OAuth\Domain\Enum\OAuthClient\GrantType;
use App\OAuth\Domain\Event\OAuthClient\OAuthClientCreated;
use App\OAuth\Domain\ValueObject\ClientSecretHash;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Uid\UuidV7;

#[ORM\Entity]
#[ORM\Table(name: 'oauth_clients')]
class OAuthClient
{
    #[ORM\Id]
    #[ORM\Column(type: 'uuid')]
    private UuidV7 $id;

    #[ORM\Column(name: 'owner_id', type: 'uuid')]
    private UuidV7 $ownerId;

    #[ORM\Column(type: 'string', length: 255)]
    private string $name;

    /** @var array<string> */
    #[ORM\Column(name: 'redirect_uris', type: '_text')]
    private array $redirectUris = [];

    #[ORM\Column(name: 'grant_types', type: '_text')]
    private array $grantTypes = [];

    #[ORM\Column(name: 'is_confidential', type: 'boolean')]
    private bool $isConfidential = true;

    #[ORM\Embedded(class: ClientSecretHash::class)]
    private ?ClientSecretHash $clientSecretHash = null;

    private array $domainEvents = [];

    // region Constructor
    private function __construct(
        UuidV7 $id,
        UuidV7 $ownerId,
        string $name,
        array $redirectUris,
        array $grantTypes,
        bool $isConfidential,
        ClientSecretHash $clientSecretHash
    ) {
        $this->id = $id;
        $this->ownerId = $ownerId;
        $this->name = $name;
        $this->redirectUris = $redirectUris;
        $this->grantTypes = $grantTypes;
        $this->isConfidential = $isConfidential;
        $this->clientSecretHash = $clientSecretHash;
    }

    /**
     * @param UuidV7 $ownerId
     * @param string $name
     * @param array<string> $redirectUris
     * @param array<GrantType> $grantTypes
     * @param bool $isConfidential
     * @param ClientSecretHash $clientSecretHash
     * @return self
     */
    public static function create(
        UuidV7 $ownerId,
        string $name,
        array $redirectUris,
        array $grantTypes,
        bool $isConfidential,
        ClientSecretHash $clientSecretHash
    ): self {
        $grantTypes = array_filter(array_map(fn (string|GrantType $gt) => $gt instanceof GrantType ? $gt->value : GrantType::tryFrom($gt)?->value, $grantTypes));

        $client = new self(
            id: new UuidV7(),
            ownerId: $ownerId,
            name: $name,
            redirectUris: $redirectUris,
            grantTypes: $grantTypes,
            isConfidential: $isConfidential,
            clientSecretHash: $clientSecretHash
        );
        $client->domainEvents[] = new OAuthClientCreated($client->id);

        return $client;
    }
    // endregion

    // region Getters && Setters
    /**
     * @return UuidV7
     */
    public function getId(): UuidV7
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return array
     */
    public function getRedirectUris(): array
    {
        return $this->redirectUris;
    }

    /**
     * @return array<GrantType>
     */
    public function getGrantTypes(): array
    {
        return array_map(fn (string $gt) => GrantType::from($gt), $this->grantTypes);
    }

    /**
     * @return bool
     */
    public function isConfidential(): bool
    {
        return $this->isConfidential;
    }

    /**
     * @return ClientSecretHash|null
     */
    public function getClientSecretHash(): ?ClientSecretHash
    {
        return $this->clientSecretHash;
    }

    public function pullDomainEvents(): array
    {
        $events = $this->domainEvents;
        $this->domainEvents = [];
        return $events;
    }
    // endregion
}
