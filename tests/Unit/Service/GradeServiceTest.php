<?php

namespace App\Tests\Unit\Service;

use App\Entity\Course;
use App\Entity\Grade;
use App\Entity\User;
use App\Repository\GradeRepository;
use App\Service\GradeService;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;

class GradeServiceTest extends TestCase
{
    private GradeService $gradeService;
    private $em;
    private $gradeRepository;
    private User $student;
    private Course $course;

    protected function setUp(): void
    {
        $this->em = $this->createMock(EntityManagerInterface::class);
        $this->gradeRepository = $this->getMockBuilder(GradeRepository::class)
            ->disableOriginalConstructor()
            ->getMock();
        
        $this->gradeService = new GradeService($this->em, $this->gradeRepository);

        // Create test entities
        $this->student = new User();
        $this->student->setEmail('student@test.com');

        $this->course = new Course();
        $this->course->setTitle('Test Course');
    }

    /**
     * Test adding a valid grade
     */
    public function testAddGradeWithValidData(): void
    {
        $this->em->expects($this->once())->method('persist');
        $this->em->expects($this->once())->method('flush');

        $grade = $this->gradeService->addGrade($this->student, $this->course, 15.5, 'exam', 2);

        $this->assertInstanceOf(Grade::class, $grade);
        $this->assertEquals(15.5, $grade->getValue());
        $this->assertEquals('exam', $grade->getType());
        $this->assertEquals(2, $grade->getCoefficient());
        $this->assertEquals($this->student, $grade->getStudent());
        $this->assertEquals($this->course, $grade->getCourse());
    }

    /**
     * Test adding a grade with value too low
     */
    public function testAddGradeWithValueTooLow(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Grade must be between 0 and 20');

        $this->gradeService->addGrade($this->student, $this->course, -1, 'exam');
    }

    /**
     * Test adding a grade with value too high
     */
    public function testAddGradeWithValueTooHigh(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Grade must be between 0 and 20');

        $this->gradeService->addGrade($this->student, $this->course, 21, 'exam');
    }

    /**
     * Test adding a grade with invalid type
     */
    public function testAddGradeWithInvalidType(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Grade type must be one of');

        $this->gradeService->addGrade($this->student, $this->course, 15, 'invalid_type');
    }

    /**
     * Test adding a grade with invalid coefficient
     */
    public function testAddGradeWithInvalidCoefficient(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Coefficient must be at least 1');

        $this->gradeService->addGrade($this->student, $this->course, 15, 'exam', 0);
    }

    /**
     * Test updating a grade
     */
    public function testUpdateGrade(): void
    {
        $grade = new Grade();
        $grade->setValue(10);
        $grade->setType('exam');
        $grade->setCoefficient(1);

        $this->em->expects($this->once())->method('flush');

        $this->gradeService->updateGrade($grade, 16.5, 'assignment', 2);

        $this->assertEquals(16.5, $grade->getValue());
        $this->assertEquals('assignment', $grade->getType());
        $this->assertEquals(2, $grade->getCoefficient());
    }

    /**
     * Test updating only the grade value
     */
    public function testUpdateGradeValueOnly(): void
    {
        $grade = new Grade();
        $grade->setValue(10);
        $grade->setType('exam');
        $grade->setCoefficient(2);

        $this->em->expects($this->once())->method('flush');

        $this->gradeService->updateGrade($grade, 18);

        $this->assertEquals(18, $grade->getValue());
        $this->assertEquals('exam', $grade->getType());
        $this->assertEquals(2, $grade->getCoefficient());
    }

    /**
     * Test deleting a grade
     */
    public function testDeleteGrade(): void
    {
        $grade = new Grade();

        $this->em->expects($this->once())->method('remove')->with($grade);
        $this->em->expects($this->once())->method('flush');

        $this->gradeService->deleteGrade($grade);
    }

    /**
     * Test getting grades by student and course
     */
    public function testGetGradesByStudentAndCourse(): void
    {
        $grades = [new Grade(), new Grade()];
        
        $this->gradeRepository
            ->expects($this->once())
            ->method('findByStudentAndCourse')
            ->with($this->student, $this->course)
            ->willReturn($grades);

        $result = $this->gradeService->getGradesByStudentAndCourse($this->student, $this->course);

        $this->assertEquals($grades, $result);
    }

    /**
     * Test getting grades by course
     */
    public function testGetGradesByCourse(): void
    {
        $grades = [new Grade(), new Grade(), new Grade()];
        
        $this->gradeRepository
            ->expects($this->once())
            ->method('findByCourse')
            ->with($this->course)
            ->willReturn($grades);

        $result = $this->gradeService->getGradesByCourse($this->course);

        $this->assertEquals($grades, $result);
    }

    /**
     * Test checking if student has grades in course
     */
    public function testHasGradesInCourse(): void
    {
        $this->gradeRepository
            ->expects($this->once())
            ->method('countByStudentAndCourse')
            ->with($this->student, $this->course)
            ->willReturn(3);

        $result = $this->gradeService->hasGradesInCourse($this->student, $this->course);

        $this->assertTrue($result);
    }

    /**
     * Test getting grades by type
     */
    public function testGetGradesByType(): void
    {
        $grades = [new Grade(), new Grade()];
        
        $this->gradeRepository
            ->expects($this->once())
            ->method('findByCourseAndType')
            ->with($this->course, 'exam')
            ->willReturn($grades);

        $result = $this->gradeService->getGradesByType($this->course, 'exam');

        $this->assertEquals($grades, $result);
    }

    /**
     * Test valid grade types
     */
    public function testValidGradeTypes(): void
    {
        $validTypes = ['exam', 'assignment', 'participation', 'project'];

        $this->em->expects($this->exactly(count($validTypes)))->method('persist');
        $this->em->expects($this->exactly(count($validTypes)))->method('flush');

        foreach ($validTypes as $type) {
            $grade = $this->gradeService->addGrade($this->student, $this->course, 12, $type);
            $this->assertEquals($type, $grade->getType());
        }
    }
}
