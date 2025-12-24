<?php

namespace App\Tests\Integration\Repository;

use App\Entity\Course;
use App\Entity\Enrollment;
use App\Entity\Grade;
use App\Entity\User;
use App\Repository\EnrollmentRepository;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class EnrollmentRepositoryTest extends KernelTestCase
{
    private EnrollmentRepository $enrollmentRepository;

    protected function setUp(): void
    {
        self::bootKernel();
        $this->enrollmentRepository = self::getContainer()->get(EnrollmentRepository::class);
    }

    public function testFindEnrollment(): void
    {
        $user = new User();
        $user->setEmail('test@example.com');
        $user->setPassword('hashed');
        $user->setName('Test Student');
        $user->setRoles(['ROLE_STUDENT']);

        $course = new Course();
        $course->setTitle('Test Course');
        $course->setDescription('Description');

        $enrollment = new Enrollment();
        $enrollment->setStudent($user);
        $enrollment->setCourse($course);
        $enrollment->setEnrolledAt(new \DateTime());

        $em = self::getContainer()->get('doctrine')->getManager();
        $em->persist($user);
        $em->persist($course);
        $em->persist($enrollment);
        $em->flush();

        $found = $this->enrollmentRepository->findEnrollment($user, $course);

        $this->assertNotNull($found);
        $this->assertEquals($enrollment->getId(), $found->getId());
    }

    public function testFindEnrollmentNotFound(): void
    {
        $user = new User();
        $user->setEmail('test2@example.com');
        $user->setPassword('hashed');
        $user->setName('Test Student 2');
        $user->setRoles(['ROLE_STUDENT']);

        $course = new Course();
        $course->setTitle('Test Course 2');
        $course->setDescription('Description 2');

        $em = self::getContainer()->get('doctrine')->getManager();
        $em->persist($user);
        $em->persist($course);
        $em->flush();

        $found = $this->enrollmentRepository->findEnrollment($user, $course);

        $this->assertNull($found);
    }

    public function testFindEnrollmentsByCourse(): void
    {
        $course = new Course();
        $course->setTitle('Course A');
        $course->setDescription('Description A');

        $student1 = new User();
        $student1->setEmail('student1@test.com');
        $student1->setPassword('hashed');
        $student1->setName('Student 1');
        $student1->setRoles(['ROLE_STUDENT']);

        $student2 = new User();
        $student2->setEmail('student2@test.com');
        $student2->setPassword('hashed');
        $student2->setName('Student 2');
        $student2->setRoles(['ROLE_STUDENT']);

        $enrollment1 = new Enrollment();
        $enrollment1->setStudent($student1);
        $enrollment1->setCourse($course);
        $enrollment1->setEnrolledAt(new \DateTime());

        $enrollment2 = new Enrollment();
        $enrollment2->setStudent($student2);
        $enrollment2->setCourse($course);
        $enrollment2->setEnrolledAt(new \DateTime());

        $em = self::getContainer()->get('doctrine')->getManager();
        $em->persist($course);
        $em->persist($student1);
        $em->persist($student2);
        $em->persist($enrollment1);
        $em->persist($enrollment2);
        $em->flush();

        $enrollments = $this->enrollmentRepository->findByCourse($course);

        $this->assertCount(2, $enrollments);
    }
}
