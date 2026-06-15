<?php

declare(strict_types=1);

namespace App\Tests\Functional\Identity;

use App\Tests\Double\InMemoryUserRepository;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

final class RegisterUserControllerTest extends WebTestCase
{
    private const string ENDPOINT = '/api/identity/register';
    private const string VALID_EMAIL = 'alice@example.com';
    private const string VALID_PASSWORD = 'Str0ng!Pass#99';

    protected function setUp(): void
    {
        parent::setUp();
        InMemoryUserRepository::reset();
    }

    public function testRegisterSucceeds(): void
    {
        $client = static::createClient();

        $client->request('POST', self::ENDPOINT, content: json_encode([
            'email' => self::VALID_EMAIL,
            'password' => self::VALID_PASSWORD,
        ]));

        self::assertResponseStatusCodeSame(Response::HTTP_CREATED);
    }

    public function testRegisterFailsOnInvalidEmail(): void
    {
        $client = static::createClient();

        $client->request('POST', self::ENDPOINT, content: json_encode([
            'email' => 'not-an-email',
            'password' => self::VALID_PASSWORD,
        ]));

        self::assertResponseStatusCodeSame(Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    public function testRegisterFailsOnShortPassword(): void
    {
        $client = static::createClient();

        $client->request('POST', self::ENDPOINT, content: json_encode([
            'email' => self::VALID_EMAIL,
            'password' => 'Short1!',
        ]));

        self::assertResponseStatusCodeSame(Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    public function testRegisterFailsOnWeakPassword(): void
    {
        $client = static::createClient();

        $client->request('POST', self::ENDPOINT, content: json_encode([
            'email' => self::VALID_EMAIL,
            'password' => '12345678',
        ]));

        self::assertResponseStatusCodeSame(Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    public function testRegisterFailsOnDuplicateEmail(): void
    {
        $client = static::createClient();

        $payload = json_encode([
            'email' => self::VALID_EMAIL,
            'password' => self::VALID_PASSWORD,
        ]);

        $client->request('POST', self::ENDPOINT, content: $payload);
        self::assertResponseStatusCodeSame(Response::HTTP_CREATED);

        $client->request('POST', self::ENDPOINT, content: $payload);
        self::assertResponseStatusCodeSame(Response::HTTP_CONFLICT);
    }
}
