<?php

declare(strict_types=1);

namespace App\Tests\Functional\Identity;

use App\Identity\Domain\Entity\User;
use App\Identity\Domain\ValueObject\Email;
use App\Identity\Domain\ValueObject\HashedPassword;
use App\Tests\Double\InMemoryUserRepository;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

final class LoginUserControllerTest extends WebTestCase
{
    private const string ENDPOINT = '/api/identity/login';
    private const string SEEDED_EMAIL = 'bob@example.com';
    private const string SEEDED_PASSWORD = 'Str0ng!Pass#99';

    private KernelBrowser $client;

    protected function setUp(): void
    {
        parent::setUp();
        InMemoryUserRepository::reset();

        // createClient() boots the kernel — must come before getContainer() calls
        $this->client = static::createClient();

        $this->seedUser(self::SEEDED_EMAIL, self::SEEDED_PASSWORD);
    }

    public function testLoginSucceeds(): void
    {
        $this->client->request('POST', self::ENDPOINT, content: json_encode([
            'email' => self::SEEDED_EMAIL,
            'password' => self::SEEDED_PASSWORD,
        ]));

        self::assertResponseStatusCodeSame(Response::HTTP_OK);

        $body = json_decode($this->client->getResponse()->getContent(), true);
        self::assertArrayHasKey('access_token', $body);
        self::assertArrayHasKey('token_type', $body);
        self::assertSame('Bearer', $body['token_type']);
        self::assertNotEmpty($body['access_token']);
    }

    public function testLoginFailsOnWrongPassword(): void
    {
        $this->client->request('POST', self::ENDPOINT, content: json_encode([
            'email' => self::SEEDED_EMAIL,
            'password' => 'WrongPassword!1',
        ]));

        self::assertResponseStatusCodeSame(Response::HTTP_CONFLICT);

        $body = json_decode($this->client->getResponse()->getContent(), true);
        self::assertSame('Invalid credentials', $body['error']);
    }

    public function testLoginFailsOnUnknownEmail(): void
    {
        $this->client->request('POST', self::ENDPOINT, content: json_encode([
            'email' => 'ghost@example.com',
            'password' => self::SEEDED_PASSWORD,
        ]));

        self::assertResponseStatusCodeSame(Response::HTTP_CONFLICT);
    }

    public function testLoginFailsOnBlankFields(): void
    {
        $this->client->request('POST', self::ENDPOINT, content: json_encode([
            'email' => '',
            'password' => '',
        ]));

        self::assertResponseStatusCodeSame(Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    // -------------------------------------------------------------------------

    private function seedUser(string $email, string $password): void
    {
        /** @var InMemoryUserRepository $repo */
        $repo = static::getContainer()->get(InMemoryUserRepository::class);

        /** @var UserPasswordHasherInterface $hasher */
        $hasher = static::getContainer()->get(UserPasswordHasherInterface::class);

        $user = User::register(new Email($email));
        $user->setPassword(HashedPassword::fromHash($hasher->hashPassword($user, $password)));
        $repo->save($user);
    }
}
