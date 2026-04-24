<?php

namespace App\Tests\Functional;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class AuthControllerTest extends WebTestCase
{
    private KernelBrowser $client;
    private EntityManagerInterface $em;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->em = static::getContainer()->get(EntityManagerInterface::class);
        $this->em->getConnection()->executeStatement('DELETE FROM `user`');
    }

    private function json(string $method, string $uri, array $body = []): array
    {
        $this->client->request(
            $method,
            $uri,
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($body)
        );

        return json_decode($this->client->getResponse()->getContent(), true) ?? [];
    }

    private function register(string $email = 'user@example.com', string $password = 'password123'): array
    {
        return $this->json('POST', '/api/auth/register', ['email' => $email, 'password' => $password]);
    }

    private function login(string $email = 'user@example.com', string $password = 'password123'): array
    {
        return $this->json('POST', '/api/auth/login', ['email' => $email, 'password' => $password]);
    }

    // --- Register ---

    public function testRegisterSuccess(): void
    {
        $this->register();
        $this->assertResponseStatusCodeSame(201);
    }

    public function testRegisterReturnsMeaningfulMessage(): void
    {
        $data = $this->register();
        $this->assertArrayHasKey('message', $data);
    }

    public function testRegisterDuplicateEmailReturns409(): void
    {
        $this->register();
        $this->register();
        $this->assertResponseStatusCodeSame(409);
    }

    public function testRegisterPasswordTooShortReturns400(): void
    {
        $this->register('user@example.com', 'short');
        $this->assertResponseStatusCodeSame(400);
    }

    public function testRegisterInvalidEmailReturns400(): void
    {
        $this->register('not-an-email', 'password123');
        $this->assertResponseStatusCodeSame(400);
    }

    // --- Login ---

    public function testLoginSuccess(): void
    {
        $this->register();
        $data = $this->login();
        $this->assertResponseIsSuccessful();
        $this->assertArrayHasKey('token', $data);
        $this->assertNotEmpty($data['token']);
    }

    public function testLoginWrongPasswordReturns401(): void
    {
        $this->register();
        $this->login('user@example.com', 'wrongpassword');
        $this->assertResponseStatusCodeSame(401);
    }

    public function testLoginUnknownEmailReturns401(): void
    {
        $this->login('nobody@example.com', 'password123');
        $this->assertResponseStatusCodeSame(401);
    }

    // --- /me ---

    public function testMeWithoutTokenReturns401(): void
    {
        $this->client->request('GET', '/api/auth/me');
        $this->assertResponseStatusCodeSame(401);
    }

    public function testMeWithValidTokenReturnsUserData(): void
    {
        $this->register('me@example.com');
        $token = $this->login('me@example.com')['token'];

        $this->client->request('GET', '/api/auth/me', [], [], [
            'HTTP_AUTHORIZATION' => 'Bearer ' . $token,
        ]);

        $this->assertResponseIsSuccessful();
        $data = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertSame('me@example.com', $data['email']);
        $this->assertContains('ROLE_USER', $data['roles']);
        $this->assertFalse($data['isAdmin']);
        $this->assertFalse($data['isPaying']);
        $this->assertTrue($data['isVerified']);
    }

    public function testMeWithInvalidTokenReturns401(): void
    {
        $this->client->request('GET', '/api/auth/me', [], [], [
            'HTTP_AUTHORIZATION' => 'Bearer invalid.token.here',
        ]);
        $this->assertResponseStatusCodeSame(401);
    }
}
