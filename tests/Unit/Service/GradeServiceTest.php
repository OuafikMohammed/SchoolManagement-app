<?php

namespace App\Tests\Unit\Service;

use App\Entity\Course;
use App\Entity\Grade;
use App\Entity\User;
use App\Repository\GradeRepository;
use App\Service\GradeService;
use PHPUnit\Framework\TestCase;

class GradeServiceTest extends TestCase
{
    private GradeService $gradeService;
    private GradeRepository $gradeRepository;

    protected function setUp(): void
    {
        $this->gradeRepository = $this->createMock(GradeRepository::class);
        $this->gradeService = new GradeService($this->gradeRepository);
    }

    public function testAddGradeWithValidData(): void
    {
        $student = new User();
        $course = new Course();
        
        $result = $this->gradeService->addGrade($student, $course, 15, 'exam', 2);

        $this->assertInstanceOf(Grade::class, $result);
        $this->assertEquals(15, $result->getValue());
        $this->assertEquals('exam', $result->getType());
        $this->assertEquals(2, $result->getCoefficient());
    }

    public function testAddGradeValidatesMinValue(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        
        $student = new User();
        $course = new Course();
        $this->gradeService->addGrade($student, $course, -1, 'exam', 1);
    }

    public function testAddGradeValidatesMaxValue(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        
        $student = new User();
        $course = new Course();
        $this->gradeService->addGrade($student, $course, 21, 'exam', 1);
    }

    public function testUpdateGradeChangesValue(): void
    {
        $grade = new Grade();
        $grade->setValue(10);
        $grade->setType('homework');

        $updated = $this->gradeService->updateGrade($grade, 18, 'exam', 3);

        $this->assertEquals(18, $updated->getValue());
        $this->assertEquals('exam', $updated->getType());
        $this->assertEquals(3, $updated->getCoefficient());
    }

    public function testDeleteGradeRemovesFromRepository(): void
    {
        $grade = new Grade();
        
        $this->gradeRepository->expects($this->once())
            ->method('remove')
            ->with($grade, true);

        $this->gradeService->deleteGrade($grade);
    }
}
