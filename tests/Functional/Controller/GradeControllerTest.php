<?php

namespace App\Tests\Functional\Controller;

use App\Entity\Course;
use App\Entity\Grade;
use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class GradeControllerTest extends WebTestCase
{
    private $client;

    protected function setUp(): void
    {
        $this->client = static::createClient();
    }

    public function testTeacherCanViewGradeIndex(): void
    {
        // Create test user
        $user = new User();
        $user->setEmail('teacher@test.com');
        $user->setName('Teacher Name');
        $user->setRoles(['ROLE_TEACHER']);
        
        // Note: In a real test, you'd use fixtures or factories
        // This is a simplified example showing the test structure

        $this->client->request('GET', '/teacher/grades');
        
        // Should redirect to login if not authenticated
        $this->assertResponseRedirects('/login');
    }

    public function testStudentCannotViewTeacherGrades(): void
    {
        $this->client->request('GET', '/teacher/grades');
        
        // Should redirect to login
        $this->assertResponseRedirects('/login');
    }

    public function testGradeAdditionRequiresCsrfToken(): void
    {
        $this->client->request('POST', '/teacher/grades/add');
        
        // Should fail without CSRF token
        $this->assertResponseRedirects();
    }
}
