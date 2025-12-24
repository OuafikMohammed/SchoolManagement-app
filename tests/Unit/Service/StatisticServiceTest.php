<?php

namespace App\Tests\Unit\Service;

use App\Entity\Course;
use App\Entity\Grade;
use App\Entity\User;
use App\Repository\GradeRepository;
use App\Repository\StatisticRepository;
use App\Service\StatisticService;
use PHPUnit\Framework\TestCase;

class StatisticServiceTest extends TestCase
{
    private StatisticService $statisticService;
    private GradeRepository $gradeRepository;
    private StatisticRepository $statisticRepository;

    protected function setUp(): void
    {
        $this->gradeRepository = $this->createMock(GradeRepository::class);
        $this->statisticRepository = $this->createMock(StatisticRepository::class);
        $this->statisticService = new StatisticService($this->gradeRepository, $this->statisticRepository);
    }

    /**
     * Test calculating average for student in course
     */
    public function testCalculateAverageForStudentInCourse(): void
    {
        $student = new User();
        $course = new Course();

        // (16*1 + 14*2) / (1+2) = (16 + 28) / 3 = 14.67
        $this->statisticRepository->expects($this->once())
            ->method('calculateAverageGrade')
            ->with($student, $course)
            ->willReturn(14.67);

        $average = $this->statisticService->calculateAverageForStudentInCourse($student, $course);

        $this->assertEqualsWithDelta(14.67, $average, 0.01);
    }

    /**
     * Test average returns zero for no grades
     */
    public function testCalculateAverageReturnsZeroForNoGrades(): void
    {
        $student = new User();
        $course = new Course();

        $this->statisticRepository->expects($this->once())
            ->method('calculateAverageGrade')
            ->with($student, $course)
            ->willReturn(null);

        $average = $this->statisticService->calculateAverageForStudentInCourse($student, $course);

        $this->assertEquals(0, $average);
    }

    /**
     * Test calculating overall average for student
     */
    public function testCalculateOverallAverage(): void
    {
        $student = new User();

        $grade1 = new Grade();
        $grade1->setValue(18);
        $grade1->setCoefficient(2);

        $grade2 = new Grade();
        $grade2->setValue(16);
        $grade2->setCoefficient(1);

        $grade3 = new Grade();
        $grade3->setValue(20);
        $grade3->setCoefficient(1);

        $this->gradeRepository->expects($this->once())
            ->method('findByStudent')
            ->with($student)
            ->willReturn([$grade1, $grade2, $grade3]);

        $average = $this->statisticService->calculateOverallAverage($student);

        // (18*2 + 16*1 + 20*1) / (2+1+1) = (36 + 16 + 20) / 4 = 72/4 = 18
        $this->assertEquals(18.0, $average);
    }

    /**
     * Test getting course ranking
     */
    public function testGetCourseRanking(): void
    {
        $course = new Course();

        $rankedStudents = [
            [
                'student_id' => 1,
                'name' => 'Alice',
                'email' => 'alice@test.com',
                'average' => 18.5,
                'grade_count' => 5,
            ],
            [
                'student_id' => 2,
                'name' => 'Bob',
                'email' => 'bob@test.com',
                'average' => 15.0,
                'grade_count' => 5,
            ],
        ];

        $this->statisticRepository->expects($this->once())
            ->method('getRankedStudentsByCourse')
            ->with($course)
            ->willReturn($rankedStudents);

        $ranking = $this->statisticService->getCourseRanking($course);

        $this->assertEquals(2, count($ranking));
        $this->assertEquals(1, $ranking[0]['rank']);
        $this->assertEquals(18.5, $ranking[0]['average']);
        $this->assertEquals(2, $ranking[1]['rank']);
        $this->assertEquals(15.0, $ranking[1]['average']);
    }

    /**
     * Test getting student ranking position
     */
    public function testGetStudentRankingPosition(): void
    {
        $student = new User();
        $student->setId(1);
        $course = new Course();

        $rankedStudents = [
            [
                'student_id' => 1,
                'name' => 'Alice',
                'email' => 'alice@test.com',
                'average' => 18.5,
                'grade_count' => 5,
            ],
            [
                'student_id' => 2,
                'name' => 'Bob',
                'email' => 'bob@test.com',
                'average' => 15.0,
                'grade_count' => 5,
            ],
        ];

        $this->statisticRepository->expects($this->once())
            ->method('getRankedStudentsByCourse')
            ->with($course)
            ->willReturn($rankedStudents);

        $position = $this->statisticService->getStudentRankingPosition($student, $course);

        $this->assertNotNull($position);
        $this->assertEquals(1, $position['rank']);
        $this->assertEquals(18.5, $position['average']);
    }

    /**
     * Test getting averages by type
     */
    public function testGetAveragesByType(): void
    {
        $student = new User();
        $course = new Course();

        $typeAverages = [
            ['type' => 'exam', 'average' => 17.5, 'count' => 2],
            ['type' => 'assignment', 'average' => 19.0, 'count' => 3],
            ['type' => 'participation', 'average' => 18.0, 'count' => 4],
        ];

        $this->statisticRepository->expects($this->once())
            ->method('getAveragesByType')
            ->with($student, $course)
            ->willReturn($typeAverages);

        $result = $this->statisticService->getAveragesByType($student, $course);

        $this->assertEquals(3, count($result));
        $this->assertEquals(17.5, $result['exam']['average']);
        $this->assertEquals(2, $result['exam']['count']);
        $this->assertEquals(19.0, $result['assignment']['average']);
    }

    /**
     * Test getting class statistics
     */
    public function testGetClassStatistics(): void
    {
        $course = new Course();

        $stats = [
            [
                'min_grade' => 8.0,
                'max_grade' => 20.0,
                'average_grade' => 15.5,
                'student_count' => 25,
                'total_grades' => 125,
            ],
        ];

        $this->statisticRepository->expects($this->once())
            ->method('getClassStatistics')
            ->with($course)
            ->willReturn($stats);

        $result = $this->statisticService->getClassStatistics($course);

        $this->assertEquals(8.0, $result['min_grade']);
        $this->assertEquals(20.0, $result['max_grade']);
        $this->assertEquals(15.5, $result['average_grade']);
        $this->assertEquals(25, $result['student_count']);
        $this->assertEquals(125, $result['total_grades']);
    }

    /**
     * Test getting class statistics with no grades
     */
    public function testGetClassStatisticsWithNoGrades(): void
    {
        $course = new Course();

        $this->statisticRepository->expects($this->once())
            ->method('getClassStatistics')
            ->with($course)
            ->willReturn([]);

        $result = $this->statisticService->getClassStatistics($course);

        $this->assertEquals(0, $result['min_grade']);
        $this->assertEquals(0, $result['max_grade']);
        $this->assertEquals(0, $result['average_grade']);
        $this->assertEquals(0, $result['student_count']);
    }

    /**
     * Test getting grade distribution
     */
    public function testGetGradeDistribution(): void
    {
        $course = new Course();

        $distribution = [
            ['grade_range' => 'Excellent (18-20)', 'count' => 15, 'percentage' => 30.0],
            ['grade_range' => 'Very Good (15-17)', 'count' => 20, 'percentage' => 40.0],
            ['grade_range' => 'Good (12-14)', 'count' => 10, 'percentage' => 20.0],
            ['grade_range' => 'Average (10-11)', 'count' => 5, 'percentage' => 10.0],
        ];

        $this->statisticRepository->expects($this->once())
            ->method('getGradeDistribution')
            ->with($course)
            ->willReturn($distribution);

        $result = $this->statisticService->getGradeDistribution($course);

        $this->assertEquals(4, count($result));
        $this->assertEquals(30.0, $result[0]['percentage']);
    }

    /**
     * Test getting student progress
     */
    public function testGetStudentProgress(): void
    {
        $student = new User();
        $course = new Course();

        $grade1 = new Grade();
        $grade1->setValue(12);

        $grade2 = new Grade();
        $grade2->setValue(14);

        $grade3 = new Grade();
        $grade3->setValue(16);

        $this->gradeRepository->expects($this->once())
            ->method('findByStudentAndCourse')
            ->with($student, $course)
            ->willReturn([$grade1, $grade2, $grade3]);

        $this->statisticRepository->expects($this->once())
            ->method('calculateAverageGrade')
            ->with($student, $course)
            ->willReturn(14.0);

        $result = $this->statisticService->getStudentProgress($student, $course);

        $this->assertEquals(3, $result['total_grades']);
        $this->assertEquals(14.0, $result['average']);
        $this->assertEquals(12, $result['min_grade']);
        $this->assertEquals(16, $result['max_grade']);
    }

    /**
     * Test recalculate all statistics
     */
    public function testRecalculateAll(): void
    {
        $course = new Course();

        $rankedStudents = [
            ['student_id' => 1, 'name' => 'Alice', 'email' => 'alice@test.com', 'average' => 18.5, 'grade_count' => 5],
        ];

        $stats = [
            ['min_grade' => 8.0, 'max_grade' => 20.0, 'average_grade' => 15.5, 'student_count' => 25, 'total_grades' => 125],
        ];

        $distribution = [
            ['grade_range' => 'Excellent (18-20)', 'count' => 15, 'percentage' => 30.0],
        ];

        $this->statisticRepository->expects($this->exactly(2))
            ->method('getRankedStudentsByCourse')
            ->with($course)
            ->willReturn($rankedStudents);

        $this->statisticRepository->expects($this->once())
            ->method('getClassStatistics')
            ->with($course)
            ->willReturn($stats);

        $this->statisticRepository->expects($this->once())
            ->method('getGradeDistribution')
            ->with($course)
            ->willReturn($distribution);

        $result = $this->statisticService->recalculateAll($course);

        $this->assertArrayHasKey('ranking', $result);
        $this->assertArrayHasKey('statistics', $result);
        $this->assertArrayHasKey('distribution', $result);
    }
}
