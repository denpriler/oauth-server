<?php

declare(strict_types=1);

namespace App\OAuth\Application\Command\Authorize;

use App\OAuth\Domain\Entity\AuthorizationCode;
use App\OAuth\Domain\Enum\OAuthClient\GrantType;
use App\OAuth\Domain\Repository\AuthorizationCodeRepositoryInterface;
use App\OAuth\Domain\Repository\OAuthClientRepositoryInterface;
use DomainException;
use Random\RandomException;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Uid\UuidV7;

#[AsMessageHandler]
final readonly class AuthorizeHandler
{
    public function __construct(
        private AuthorizationCodeRepositoryInterface $authorizationCodeRepository,
        private OAuthClientRepositoryInterface $oauthClientRepository,
    ) {
    }

    /**
     * @throws RandomException
     */
    public function __invoke(AuthorizeCommand $command): array
    {
        $clientId = UuidV7::fromString($command->clientId);

        $oAuthClient = $this->oauthClientRepository->findById($clientId);
        if (!$oAuthClient) {
            throw new DomainException('client_not_found');
        }

        if (!in_array($command->redirectUri, $oAuthClient->getRedirectUris())) {
            throw new DomainException('invalid_redirect_uri');
        }

        if (!in_array(GrantType::AUTHORIZATION_CODE, $oAuthClient->getGrantTypes())) {
            throw new DomainException('unsupported_grant_type');
        }

        $code = bin2hex(random_bytes(32));

        $authorizationCode = AuthorizationCode::create(
            clientId: $clientId,
            userId: $command->userId,
            redirectUri: $command->redirectUri,
            code: $code,
            codeChallenge: $command->codeChallenge,
            codeChallengeMethod: $command->codeChallengeMethod,
            scopes: $command->scopes,
            state: $command->state,
        );
        $this->authorizationCodeRepository->save($authorizationCode);

        return [
            'redirect_url' => $command->redirectUri . '?' . http_build_query(array_filter([
                'code' => $code,
                'state' => $command->state,
            ])),
        ];
    }
}
