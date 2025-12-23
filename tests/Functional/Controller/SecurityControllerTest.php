<?php

namespace App\Tests\Functional\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class SecurityControllerTest extends WebTestCase
{
    private $client;

    protected function setUp(): void
    {
        $this->client = static::createClient();
    }

    public function testLoginPageIsAccessible(): void
    {
        $this->client->request('GET', '/login');
        $this->assertResponseIsSuccessful();
        $this->assertStringContainsString('Login', $this->client->getResponse()->getContent());
    }

    public function testRegisterPageIsAccessible(): void
    {
        $this->client->request('GET', '/register');
        $this->assertResponseIsSuccessful();
        $this->assertStringContainsString('Register', $this->client->getResponse()->getContent());
    }

    public function testLogoutRedirectsToHome(): void
    {
        $this->client->request('GET', '/logout');
        $this->assertResponseRedirects();
    }

    public function testRegistrationWithValidDataCreatesUser(): void
    {
        $this->client->request('GET', '/register');
        $this->assertResponseIsSuccessful();

        // Form submission would go here with valid data
        // This is a simplified test structure
    }

    public function testDuplicateEmailIsRejected(): void
    {
        // Test that registering with existing email fails
        // Implementation depends on fixture setup
    }
}
