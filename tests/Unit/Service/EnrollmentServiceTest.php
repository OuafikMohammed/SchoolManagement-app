<?php

namespace App\Tests\Unit\Service;

use App\Entity\Course;
use App\Entity\Enrollment;
use App\Entity\User;
use App\Repository\EnrollmentRepository;
use App\Service\EnrollmentService;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class EnrollmentServiceTest extends TestCase
{
    private EnrollmentService $enrollmentService;
    /** @var MockObject&EntityManagerInterface */
    private MockObject $entityManager;
    /** @var MockObject&EnrollmentRepository */
    private MockObject $enrollmentRepository;

    protected function setUp(): void
    {
        $this->entityManager = $this->getMockBuilder(EntityManagerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->enrollmentRepository = $this->getMockBuilder(EnrollmentRepository::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->enrollmentService = new EnrollmentService(
            $this->entityManager,
            $this->enrollmentRepository
        );
    }

    public function testEnrollStudentSuccess(): void
    {
        $student = new User();
        $student->setEmail('student@test.com');

        $course = new Course();
        $course->setTitle('Math');

        $this->enrollmentRepository->expects($this->once())
            ->method('findEnrollment')
            ->with($student, $course)
            ->willReturn(null);

        $this->entityManager->expects($this->once())
            ->method('persist');

        $this->entityManager->expects($this->once())
            ->method('flush');

        $result = $this->enrollmentService->enrollStudent($student, $course);

        $this->assertInstanceOf(Enrollment::class, $result);
        $this->assertEquals($student, $result->getStudent());
        $this->assertEquals($course, $result->getCourse());
    }

    public function testEnrollStudentAlreadyEnrolled(): void
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('already enrolled');

        $student = new User();
        $course = new Course();

        $existingEnrollment = new Enrollment();
        $existingEnrollment->setStudent($student);
        $existingEnrollment->setCourse($course);

        $this->enrollmentRepository->expects($this->once())
            ->method('findEnrollment')
            ->willReturn($existingEnrollment);

        $this->enrollmentService->enrollStudent($student, $course);
    }

    public function testDropStudentSuccess(): void
    {
        $student = new User();
        $course = new Course();

        $enrollment = new Enrollment();
        $enrollment->setStudent($student);
        $enrollment->setCourse($course);

        $this->enrollmentRepository->expects($this->once())
            ->method('findEnrollment')
            ->with($student, $course)
            ->willReturn($enrollment);

        $this->entityManager->expects($this->once())
            ->method('remove')
            ->with($enrollment);

        $this->entityManager->expects($this->once())
            ->method('flush');

        $this->enrollmentService->dropStudent($student, $course);
    }

    public function testDropStudentNotEnrolled(): void
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('not enrolled');

        $student = new User();
        $course = new Course();

        $this->enrollmentRepository->expects($this->once())
            ->method('findEnrollment')
            ->willReturn(null);

        $this->enrollmentService->dropStudent($student, $course);
    }
}
