<?php

namespace App\OAuth\Application\Command\CreateOAuthClient;

use App\OAuth\Domain\Entity\OAuthClient;
use App\OAuth\Domain\Repository\OAuthClientRepositoryInterface;
use App\OAuth\Domain\ValueObject\ClientSecretHash;
use Random\RandomException;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final readonly class CreateOAuthClientHandler
{
    public function __construct(
        private OAuthClientRepositoryInterface $clientRepository
    ) {
    }

    /**
     * @throws RandomException
     */
    public function __invoke(CreateOAuthClientCommand $command): array
    {
        $plain = bin2hex(random_bytes(32));
        $hash = password_hash($plain, PASSWORD_BCRYPT);

        $client = OAuthClient::create(
            ownerId: $command->ownerId,
            name: $command->name,
            redirectUris: $command->redirectUris,
            grantTypes: $command->grantTypes,
            isConfidential: $command->isConfidential,
            clientSecretHash: ClientSecretHash::fromHash($hash)
        );

        $this->clientRepository->save($client);

        return [
            'client_id' => (string) $client->getId(),
            'client_secret' => $plain,
        ];
    }
}
