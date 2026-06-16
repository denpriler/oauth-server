<?php

declare(strict_types=1);

namespace App\Tests\Functional\OAuth;

use App\Identity\Domain\Entity\User;
use App\Identity\Domain\ValueObject\Email;
use App\Identity\Domain\ValueObject\HashedPassword;
use App\Tests\Double\InMemoryOAuthClientRepository;
use App\Tests\Double\InMemoryUserRepository;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

final class CreateOAuthClientControllerTest extends WebTestCase
{
    private const string ENDPOINT = '/oauth/client/';
    private const string SEEDED_EMAIL = 'owner@example.com';
    private const string SEEDED_PASSWORD = 'Str0ng!Pass#99';

    private KernelBrowser $client;
    private string $token;

    protected function setUp(): void
    {
        parent::setUp();
        InMemoryUserRepository::reset();
        InMemoryOAuthClientRepository::reset();

        // createClient() boots the kernel — must come before getContainer() calls
        $this->client = static::createClient();

        $user = $this->seedUser(self::SEEDED_EMAIL, self::SEEDED_PASSWORD);
        $this->token = $this->generateToken($user);
    }

    public function testCreateSucceeds(): void
    {
        $this->client->request(
            'POST',
            self::ENDPOINT,
            server: ['HTTP_AUTHORIZATION' => 'Bearer ' . $this->token],
            content: json_encode($this->validPayload()),
        );

        self::assertResponseStatusCodeSame(Response::HTTP_CREATED);

        $body = json_decode($this->client->getResponse()->getContent(), true);
        self::assertArrayHasKey('client_id', $body);
        self::assertArrayHasKey('client_secret', $body);
        self::assertNotEmpty($body['client_id']);
        self::assertNotEmpty($body['client_secret']);

        // Secret stored as bcrypt hash, not plaintext
        /** @var InMemoryOAuthClientRepository $repo */
        $repo = static::getContainer()->get(InMemoryOAuthClientRepository::class);
        $clientId = \Symfony\Component\Uid\UuidV7::fromString($body['client_id']);
        $saved = $repo->findById($clientId);

        self::assertNotNull($saved);
        $hash = $saved->getClientSecretHash()->getValue();
        self::assertNotSame($body['client_secret'], $hash);
        self::assertTrue(password_verify($body['client_secret'], $hash));
    }

    public function testCreateFailsWithoutToken(): void
    {
        $this->client->request('POST', self::ENDPOINT, content: json_encode($this->validPayload()));

        self::assertResponseStatusCodeSame(Response::HTTP_UNAUTHORIZED);
    }

    public function testCreateFailsOnInvalidRedirectUri(): void
    {
        $payload = $this->validPayload();
        $payload['redirect_uris'] = ['not-a-url'];

        $this->client->request(
            'POST',
            self::ENDPOINT,
            server: ['HTTP_AUTHORIZATION' => 'Bearer ' . $this->token],
            content: json_encode($payload),
        );

        self::assertResponseStatusCodeSame(Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    public function testCreateFailsOnInvalidGrantType(): void
    {
        $payload = $this->validPayload();
        $payload['grant_types'] = ['implicit'];

        $this->client->request(
            'POST',
            self::ENDPOINT,
            server: ['HTTP_AUTHORIZATION' => 'Bearer ' . $this->token],
            content: json_encode($payload),
        );

        self::assertResponseStatusCodeSame(Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    public function testCreateFailsOnEmptyName(): void
    {
        $payload = $this->validPayload();
        $payload['name'] = '';

        $this->client->request(
            'POST',
            self::ENDPOINT,
            server: ['HTTP_AUTHORIZATION' => 'Bearer ' . $this->token],
            content: json_encode($payload),
        );

        self::assertResponseStatusCodeSame(Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    // -------------------------------------------------------------------------

    /** @return array<string, mixed> */
    private function validPayload(): array
    {
        return [
            'name' => 'My App',
            'redirect_uris' => ['https://myapp.example.com/callback'],
            'grant_types' => ['authorization_code'],
            'is_confidential' => true,
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

    private function generateToken(User $user): string
    {
        /** @var JWTTokenManagerInterface $jwtManager */
        $jwtManager = static::getContainer()->get(JWTTokenManagerInterface::class);

        return $jwtManager->create($user);
    }
}
