<?php

namespace App\Tests\Unit\Service;

use App\Entity\Course;
use App\Entity\Grade;
use App\Entity\User;
use App\Repository\GradeRepository;
use App\Service\StatisticService;
use PHPUnit\Framework\TestCase;

class StatisticServiceTest extends TestCase
{
    private StatisticService $statisticService;
    private GradeRepository $gradeRepository;

    protected function setUp(): void
    {
        $this->gradeRepository = $this->createMock(GradeRepository::class);
        $this->statisticService = new StatisticService($this->gradeRepository);
    }

    public function testCalculateAverageForStudentInCourse(): void
    {
        $student = new User();
        $course = new Course();

        $grade1 = new Grade();
        $grade1->setValue(16);
        $grade1->setCoefficient(1);

        $grade2 = new Grade();
        $grade2->setValue(14);
        $grade2->setCoefficient(2);

        $this->gradeRepository->expects($this->once())
            ->method('findByStudentAndCourse')
            ->with($student, $course)
            ->willReturn([$grade1, $grade2]);

        $average = $this->statisticService->calculateAverageForStudentInCourse($student, $course);

        // (16*1 + 14*2) / (1+2) = (16 + 28) / 3 = 44/3 = 14.67
        $this->assertEqualsWithDelta(14.67, $average, 0.01);
    }

    public function testCalculateAverageReturnsZeroForNoGrades(): void
    {
        $student = new User();
        $course = new Course();

        $this->gradeRepository->expects($this->once())
            ->method('findByStudentAndCourse')
            ->with($student, $course)
            ->willReturn([]);

        $average = $this->statisticService->calculateAverageForStudentInCourse($student, $course);

        $this->assertEquals(0, $average);
    }

    public function testGetCourseRankingOrdersStudents(): void
    {
        $student1 = new User();
        $student2 = new User();
        $course = new Course();

        // Setup grades for student 1: (18*1 + 16*1) / 2 = 17
        $grade1a = new Grade();
        $grade1a->setValue(18);
        $grade1a->setCoefficient(1);

        $grade1b = new Grade();
        $grade1b->setValue(16);
        $grade1b->setCoefficient(1);

        // Setup grades for student 2: (12*1 + 14*1) / 2 = 13
        $grade2a = new Grade();
        $grade2a->setValue(12);
        $grade2a->setCoefficient(1);

        $grade2b = new Grade();
        $grade2b->setValue(14);
        $grade2b->setCoefficient(1);

        $this->gradeRepository->expects($this->any())
            ->method('findByStudentAndCourse')
            ->willReturnCallback(function ($student, $course) use ($student1, $student2, $grade1a, $grade1b, $grade2a, $grade2b) {
                if ($student === $student1) {
                    return [$grade1a, $grade1b];
                }
                return [$grade2a, $grade2b];
            });

        $ranking = $this->statisticService->getCourseRanking($course, [$student1, $student2]);

        $this->assertEquals($student1, $ranking[0]['student']);
        $this->assertEqualsWithDelta(17, $ranking[0]['average'], 0.01);
    }
}
