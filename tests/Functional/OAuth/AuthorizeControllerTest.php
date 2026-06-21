<?php

declare(strict_types=1);

namespace App\Tests\Functional\OAuth;

use App\Identity\Domain\Entity\User;
use App\Identity\Domain\ValueObject\Email;
use App\Identity\Domain\ValueObject\HashedPassword;
use App\OAuth\Domain\Entity\OAuthClient;
use App\OAuth\Domain\Enum\OAuthClient\GrantType;
use App\OAuth\Domain\ValueObject\ClientSecretHash;
use App\Tests\Double\InMemoryAuthorizationCodeRepository;
use App\Tests\Double\InMemoryOAuthClientRepository;
use App\Tests\Double\InMemoryUserRepository;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Uid\UuidV7;

final class AuthorizeControllerTest extends WebTestCase
{
    private const string ENDPOINT = '/oauth/authorize';
    private const string REDIRECT_URI = 'https://myapp.example.com/callback';
    private const string CODE_CHALLENGE = 'E9Melhoa2OwvFrEMTJguCHaoeK1t8URWbuGJSstw-cM';

    private KernelBrowser $client;
    private string $token;
    private UuidV7 $clientId;
    private UuidV7 $userId;

    protected function setUp(): void
    {
        parent::setUp();
        InMemoryUserRepository::reset();
        InMemoryOAuthClientRepository::reset();
        InMemoryAuthorizationCodeRepository::reset();

        $this->client = static::createClient();

        $user = $this->seedUser('owner@example.com', 'Str0ng!Pass#99');
        $this->userId = $user->getId();
        $this->token = $this->generateToken($user);
        $this->clientId = $this->seedOAuthClient([GrantType::AUTHORIZATION_CODE->value]);
    }

    public function testAuthorizeSucceedsAndRedirects(): void
    {
        $this->client->request(
            'GET',
            self::ENDPOINT . '?' . http_build_query($this->validParams()),
            server: ['HTTP_AUTHORIZATION' => 'Bearer ' . $this->token],
        );

        self::assertResponseStatusCodeSame(Response::HTTP_FOUND);

        $location = $this->client->getResponse()->headers->get('Location');
        self::assertStringStartsWith(self::REDIRECT_URI . '?', $location);
        self::assertStringContainsString('code=', $location);
        self::assertStringContainsString('state=xyz', $location);

        // Extract code from Location and verify stored entity
        parse_str(parse_url($location, PHP_URL_QUERY), $query);
        $code = $query['code'];

        /** @var InMemoryAuthorizationCodeRepository $repo */
        $repo = static::getContainer()->get(InMemoryAuthorizationCodeRepository::class);
        $authCode = $repo->findByCode($code);

        self::assertNotNull($authCode);
        self::assertSame(self::CODE_CHALLENGE, $authCode->getCodeChallenge());
        self::assertSame((string) $this->userId, (string) $authCode->getUserId());
        self::assertSame(self::REDIRECT_URI, $authCode->getRedirectUri());
        self::assertFalse($authCode->isUsed());
    }

    public function testAuthorizeFailsWithoutToken(): void
    {
        $this->client->request('GET', self::ENDPOINT . '?' . http_build_query($this->validParams()));

        self::assertResponseStatusCodeSame(Response::HTTP_UNAUTHORIZED);
    }

    public function testAuthorizeFailsWithUnknownClient(): void
    {
        $params = $this->validParams();
        $params['client_id'] = (string) new UuidV7();

        $this->client->request(
            'GET',
            self::ENDPOINT . '?' . http_build_query($params),
            server: ['HTTP_AUTHORIZATION' => 'Bearer ' . $this->token],
        );

        self::assertResponseStatusCodeSame(Response::HTTP_CONFLICT);
    }

    public function testAuthorizeFailsWithWrongRedirectUri(): void
    {
        $params = $this->validParams();
        $params['redirect_uri'] = 'https://evil.example.com/callback';

        $this->client->request(
            'GET',
            self::ENDPOINT . '?' . http_build_query($params),
            server: ['HTTP_AUTHORIZATION' => 'Bearer ' . $this->token],
        );

        self::assertResponseStatusCodeSame(Response::HTTP_CONFLICT);
    }

    public function testAuthorizeFailsWhenClientLacksGrant(): void
    {
        // Seed a client with only client_credentials
        $restrictedClientId = $this->seedOAuthClient([GrantType::CLIENT_CREDENTIALS->value]);

        $params = $this->validParams();
        $params['client_id'] = (string) $restrictedClientId;

        $this->client->request(
            'GET',
            self::ENDPOINT . '?' . http_build_query($params),
            server: ['HTTP_AUTHORIZATION' => 'Bearer ' . $this->token],
        );

        self::assertResponseStatusCodeSame(Response::HTTP_CONFLICT);
    }

    public function testAuthorizeRejectsPlainCodeChallengeMethod(): void
    {
        $params = $this->validParams();
        $params['code_challenge_method'] = 'plain';

        $this->client->request(
            'GET',
            self::ENDPOINT . '?' . http_build_query($params),
            server: ['HTTP_AUTHORIZATION' => 'Bearer ' . $this->token],
        );

        self::assertResponseStatusCodeSame(Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    public function testAuthorizeRejectsInvalidScope(): void
    {
        $params = $this->validParams();
        $params['scope'] = 'profile bogus_scope';

        $this->client->request(
            'GET',
            self::ENDPOINT . '?' . http_build_query($params),
            server: ['HTTP_AUTHORIZATION' => 'Bearer ' . $this->token],
        );

        self::assertResponseStatusCodeSame(Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    public function testAuthorizeSucceedsWithoutScope(): void
    {
        $params = $this->validParams();
        unset($params['scope']);

        $this->client->request(
            'GET',
            self::ENDPOINT . '?' . http_build_query($params),
            server: ['HTTP_AUTHORIZATION' => 'Bearer ' . $this->token],
        );

        self::assertResponseStatusCodeSame(Response::HTTP_FOUND);
    }

    // -------------------------------------------------------------------------

    /** @return array<string, string> */
    private function validParams(): array
    {
        return [
            'response_type' => 'code',
            'client_id' => (string) $this->clientId,
            'redirect_uri' => self::REDIRECT_URI,
            'code_challenge' => self::CODE_CHALLENGE,
            'code_challenge_method' => 'S256',
            'scope' => 'profile email',
            'state' => 'xyz',
        ];
    }

    private function seedUser(string $email, string $password): User
    {
        /** @var InMemoryUserRepository $repo */
        $repo = static::getContainer()->get(InMemoryUserRepository::class);

        /** @var UserPasswordHasherInterface $hasher */
        $hasher = static::getContainer()->get(UserPasswordHasherInterface::class);

        $user = User::register(new Email($email));
        $user->setPassword(HashedPassword::fromHash($hasher->hashPassword($user, $password)));
        $repo->save($user);

        return $user;
    }

    /** @param array<string> $grantTypes */
    private function seedOAuthClient(array $grantTypes): UuidV7
    {
        /** @var InMemoryOAuthClientRepository $repo */
        $repo = static::getContainer()->get(InMemoryOAuthClientRepository::class);

        $ownerId = new UuidV7();
        $client = OAuthClient::create(
            ownerId: $ownerId,
            name: 'Test App',
            redirectUris: [self::REDIRECT_URI],
            grantTypes: $grantTypes,
            isConfidential: true,
            clientSecretHash: ClientSecretHash::fromHash(password_hash('secret', PASSWORD_BCRYPT)),
        );
        $repo->save($client);

        return $client->getId();
    }

    private function generateToken(User $user): string
    {
        /** @var JWTTokenManagerInterface $jwtManager */
        $jwtManager = static::getContainer()->get(JWTTokenManagerInterface::class);

        return $jwtManager->create($user);
    }
}
