<?php

namespace App\Tests\Unit\Service;

use App\Entity\Course;
use App\Entity\Enrollment;
use App\Entity\Grade;
use App\Entity\User;
use App\Repository\StatisticRepository;
use App\Service\PdfGeneratorService;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Twig\Environment;

class PdfGeneratorServiceTest extends TestCase
{
    private PdfGeneratorService $pdfService;
    /** @var MockObject&Environment */
    private MockObject $twig;
    /** @var MockObject&StatisticRepository */
    private MockObject $statisticRepository;

    protected function setUp(): void
    {
        $this->twig = $this->getMockBuilder(Environment::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->statisticRepository = $this->getMockBuilder(StatisticRepository::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->pdfService = new PdfGeneratorService(
            $this->twig,
            $this->statisticRepository
        );
    }

    public function testGenerateBulletinReturnsString(): void
    {
        // Create test student
        $student = new User();
        $student->setEmail('student@test.com');
        $student->setName('John Doe');
        $student->setPassword('hashed');
        $student->setRoles(['ROLE_STUDENT']);

        // Create test course
        $course = new Course();
        $course->setTitle('Mathematics');
        $course->setDescription('Math course');

        // Create test grade
        $grade = new Grade();
        $grade->setValue(18.5);
        $grade->setCoefficient(1);
        $grade->setType('Midterm');

        // Mock the twig render
        $this->twig->expects($this->once())
            ->method('render')
            ->willReturn('<html><body>Test</body></html>');

        // Mock statistics
        $this->statisticRepository = $this->createMock(StatisticRepository::class);
        $this->statisticRepository->expects($this->once())
            ->method('calculateAverageGrade')
            ->willReturn(18.5);

        $this->statisticRepository->expects($this->once())
            ->method('getRankedStudentsByCourse')
            ->willReturn([
                ['student_id' => 1, 'name' => 'John Doe', 'average' => 18.5],
            ]);

        $this->pdfService = new PdfGeneratorService(
            $this->twig,
            $this->statisticRepository
        );

        $result = $this->pdfService->generateBulletin($student, $course);

        $this->assertIsString($result);
        $this->assertNotEmpty($result);
    }

    public function testGenerateCourseReportReturnsString(): void
    {
        // Create test course
        $course = new Course();
        $course->setTitle('Physics');
        $course->setDescription('Physics course');

        // Mock the twig render
        $this->twig->expects($this->once())
            ->method('render')
            ->willReturn('<html><body>Test</body></html>');

        // Mock statistics for calculateAverageGrade (called in loop for each enrollment)
        $this->statisticRepository->expects($this->any())
            ->method('calculateAverageGrade')
            ->willReturn(16.0);

        $result = $this->pdfService->generateCourseReport($course);

        $this->assertIsString($result);
        $this->assertNotEmpty($result);
    }

    public function testGenerateBulletinValid(): void
    {
        // Create test student
        $student = new User();
        $student->setEmail('student@test.com');
        $student->setName('John Doe');
        $student->setPassword('hashed');
        $student->setRoles(['ROLE_STUDENT']);

        // Create test course
        $course = new Course();
        $course->setTitle('Mathematics');
        $course->setDescription('Math course');

        // Mock the twig render
        $this->twig->expects($this->once())
            ->method('render')
            ->willReturn('<html><body>Test</body></html>');

        // Mock statistics
        $this->statisticRepository->expects($this->once())
            ->method('calculateAverageGrade')
            ->willReturn(18.5);

        $this->statisticRepository->expects($this->once())
            ->method('getRankedStudentsByCourse')
            ->willReturn([
                ['student_id' => 1, 'name' => 'John Doe', 'average' => 18.5],
            ]);

        $result = $this->pdfService->generateBulletin($student, $course);

        $this->assertIsString($result);
        $this->assertNotEmpty($result);
    }
}
