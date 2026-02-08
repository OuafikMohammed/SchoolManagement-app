<?php

namespace App\Service;

use App\Entity\Course;
use App\Entity\Grade;
use App\Entity\User;
use App\Repository\GradeRepository;
use App\Repository\StatisticRepository;

class StatisticService
{
    public function __construct(
        private GradeRepository $gradeRepository,
        private StatisticRepository $statisticRepository,
    ) {
    }

    /**
     * Calculate average grade for a student in a course (weighted by coefficient).
     */
    public function calculateAverageForStudentInCourse(User $student, Course $course): float
    {
        $average = $this->statisticRepository->calculateAverageGrade($student, $course);

        return $average ?? 0;
    }

    /**
     * Calculate overall average for a student across all courses.
     */
    public function calculateOverallAverage(User $student): float
    {
        $grades = $this->gradeRepository->findByStudent($student);

        if (empty($grades)) {
            return 0;
        }

        $totalWeighted = 0;
        $totalCoeff = 0;

        foreach ($grades as $grade) {
            $coeff = $grade->getCoefficient();
            $totalWeighted += $grade->getValue() * $coeff;
            $totalCoeff += $coeff;
        }

        return $totalCoeff > 0 ? round($totalWeighted / $totalCoeff, 2) : 0;
    }

    /**
     * Get course ranking (students sorted by average grade).
     */
    public function getCourseRanking(Course $course): array
    {
        $rankedStudents = $this->statisticRepository->getRankedStudentsByCourse($course);

        $ranking = [];
        $rank = 1;
        foreach ($rankedStudents as $student) {
            $ranking[] = [
                'rank' => $rank++,
                'student_id' => $student['student_id'],
                'name' => $student['name'],
                'email' => $student['email'],
                'average' => $student['average'] ? round($student['average'], 2) : 0,
                'grade_count' => $student['grade_count'],
            ];
        }

        return $ranking;
    }

    /**
     * Get student's ranking position in a course.
     */
    public function getStudentRankingPosition(User $student, Course $course): ?array
    {
        $ranking = $this->getCourseRanking($course);

        foreach ($ranking as $entry) {
            if ($entry['student_id'] === $student->getId()) {
                return $entry;
            }
        }

        return null;
    }

    /**
     * Get grades by type (exam, homework, etc).
     */
    public function getGradesByType(User $student, Course $course, string $type): array
    {
        $grades = $this->gradeRepository->findByStudentAndCourse($student, $course);

        return array_filter($grades, fn ($g) => $g->getType() === $type);
    }

    /**
     * Get average grades grouped by type for a student in a course.
     */
    public function getAveragesByType(User $student, Course $course): array
    {
        $typeAverages = $this->statisticRepository->getAveragesByType($student, $course);

        $result = [];
        foreach ($typeAverages as $typeAvg) {
            $result[$typeAvg['type']] = [
                'average' => round($typeAvg['average'], 2),
                'count' => $typeAvg['count'],
            ];
        }

        return $result;
    }

    /**
     * Get class statistics for a course.
     */
    public function getClassStatistics(Course $course): array
    {
        $stats = $this->statisticRepository->getClassStatistics($course);

        if (empty($stats) || !$stats[0]) {
            return [
                'min_grade' => 0,
                'max_grade' => 0,
                'average_grade' => 0,
                'student_count' => 0,
                'total_grades' => 0,
            ];
        }

        return [
            'min_grade' => $stats[0]['min_grade'] ? round($stats[0]['min_grade'], 2) : 0,
            'max_grade' => $stats[0]['max_grade'] ? round($stats[0]['max_grade'], 2) : 0,
            'average_grade' => $stats[0]['average_grade'] ? round($stats[0]['average_grade'], 2) : 0,
            'student_count' => $stats[0]['student_count'],
            'total_grades' => $stats[0]['total_grades'],
        ];
    }

    /**
     * Get grade distribution in a course.
     */
    public function getGradeDistribution(Course $course): array
    {
        return $this->statisticRepository->getGradeDistribution($course);
    }

    /**
     * Get student progress statistics.
     */
    public function getStudentProgress(User $student, Course $course): array
    {
        $grades = $this->gradeRepository->findByStudentAndCourse($student, $course);

        if (empty($grades)) {
            return [
                'total_grades' => 0,
                'average' => 0,
                'min_grade' => 0,
                'max_grade' => 0,
                'trend' => null,
            ];
        }

        $values = array_map(fn ($g) => $g->getValue(), $grades);
        $average = $this->calculateAverageForStudentInCourse($student, $course);

        // Calculate trend (last 3 grades average vs first 3 grades average)
        $trend = null;
        if (count($grades) >= 6) {
            $lastThree = array_slice($values, -3);
            $firstThree = array_slice($values, 0, 3);
            $lastAvg = array_sum($lastThree) / count($lastThree);
            $firstAvg = array_sum($firstThree) / count($firstThree);
            $trend = $lastAvg > $firstAvg ? 'improving' : ($lastAvg < $firstAvg ? 'declining' : 'stable');
        }

        return [
            'total_grades' => count($grades),
            'average' => round($average, 2),
            'min_grade' => round(min($values), 2),
            'max_grade' => round(max($values), 2),
            'trend' => $trend,
        ];
    }

    /**
     * Recalculate all statistics (useful for caching or batch updates).
     */
    public function recalculateAll(Course $course): array
    {
        return [
            'ranking' => $this->getCourseRanking($course),
            'statistics' => $this->getClassStatistics($course),
            'distribution' => $this->getGradeDistribution($course),
        ];
    }
}
