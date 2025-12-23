<?php

namespace App\Service;

use App\Entity\Course;
use App\Entity\Grade;
use App\Entity\User;
use App\Repository\GradeRepository;

class StatisticService
{
    public function __construct(
        private GradeRepository $gradeRepository,
    ) {
    }

    /**
     * Calculate average grade for a student in a course
     */
    public function calculateAverageForStudentInCourse(User $student, Course $course, array $coeffs = null): float
    {
        $grades = $this->gradeRepository->findByStudentAndCourse($student, $course);

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

        return $totalCoeff > 0 ? $totalWeighted / $totalCoeff : 0;
    }

    /**
     * Calculate overall average for a student across all courses
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

        return $totalCoeff > 0 ? $totalWeighted / $totalCoeff : 0;
    }

    /**
     * Get course ranking (students sorted by average grade)
     */
    public function getCourseRanking(Course $course): array
    {
        $enrollments = $course->getEnrollments();
        $ranking = [];

        foreach ($enrollments as $enrollment) {
            $student = $enrollment->getStudent();
            $average = $this->calculateAverageForStudentInCourse($student, $course);
            $ranking[] = [
                'student' => $student,
                'average' => $average,
            ];
        }

        // Sort by average descending
        usort($ranking, fn($a, $b) => $b['average'] <=> $a['average']);

        return $ranking;
    }

    /**
     * Get grades by type (exam, homework, etc)
     */
    public function getGradesByType(User $student, Course $course, string $type): array
    {
        $grades = $this->gradeRepository->findByStudentAndCourse($student, $course);

        return array_filter($grades, fn($g) => $g->getType() === $type);
    }
}
