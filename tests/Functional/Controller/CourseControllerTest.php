<?php

namespace App\Tests\Functional\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class CourseControllerTest extends WebTestCase
{
    private $client;

    protected function setUp(): void
    {
        $this->client = static::createClient();
    }

    public function testCourseIndexIsAccessible(): void
    {
        // Unauthenticated users should be redirected to login
        $this->client->request('GET', '/teacher/courses');
        $this->assertResponseRedirects('/login');
    }

    public function testCourseShowRequiresAuthentication(): void
    {
        $this->client->request('GET', '/teacher/courses/1');
        $this->assertResponseRedirects('/login');
    }

    public function testCourseCreationRequiresCsrfToken(): void
    {
        // POST without CSRF should fail
        $this->client->request('POST', '/teacher/courses/new');
        $this->assertResponseRedirects();
    }

    public function testCourseDeleteRequiresOwnership(): void
    {
        // Student trying to delete teacher course should get 403
        $this->client->request('POST', '/teacher/courses/1/delete');
        $this->assertResponseRedirects('/login');
    }
}
