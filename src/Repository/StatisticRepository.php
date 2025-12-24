<?php

namespace App\Repository;

use App\Entity\Course;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;

class StatisticRepository
{
    public function __construct(
        private EntityManagerInterface $em,
    ) {
    }

    /**
     * Calculate average grade for a student in a course (weighted by coefficient)
     */
    public function calculateAverageGrade(User $student, Course $course): ?float
    {
        $conn = $this->em->getConnection();

        $sql = '
            SELECT SUM(g.value * g.coefficient) / SUM(g.coefficient) as average
            FROM grade g
            WHERE g.student_id = :student_id AND g.course_id = :course_id
        ';

        $result = $conn->executeQuery($sql, [
            'student_id' => $student->getId(),
            'course_id' => $course->getId(),
        ])->fetchAssociative();

        return $result && $result['average'] ? (float) $result['average'] : null;
    }

    /**
     * Get all students in a course ranked by average grade
     */
    public function getRankedStudentsByCourse(Course $course): array
    {
        $conn = $this->em->getConnection();

        $sql = '
            SELECT 
                u.id as student_id,
                u.name,
                u.email,
                SUM(g.value * g.coefficient) / SUM(g.coefficient) as average,
                COUNT(g.id) as grade_count
            FROM user u
            LEFT JOIN grade g ON u.id = g.student_id AND g.course_id = :course_id
            LEFT JOIN enrollment e ON u.id = e.student_id AND e.course_id = :course_id
            WHERE e.id IS NOT NULL
            GROUP BY u.id, u.name, u.email
            ORDER BY average DESC, u.name ASC
        ';

        return $conn->executeQuery($sql, [
            'course_id' => $course->getId(),
        ])->fetchAllAssociative();
    }

    /**
     * Get average grades by type for a student in a course
     */
    public function getAveragesByType(User $student, Course $course): array
    {
        $conn = $this->em->getConnection();

        $sql = '
            SELECT 
                g.type,
                AVG(g.value) as average,
                COUNT(g.id) as count
            FROM grade g
            WHERE g.student_id = :student_id AND g.course_id = :course_id
            GROUP BY g.type
            ORDER BY g.type ASC
        ';

        return $conn->executeQuery($sql, [
            'student_id' => $student->getId(),
            'course_id' => $course->getId(),
        ])->fetchAllAssociative();
    }

    /**
     * Get class statistics for a course
     */
    public function getClassStatistics(Course $course): array
    {
        $conn = $this->em->getConnection();

        $sql = '
            SELECT 
                MIN(g.value) as min_grade,
                MAX(g.value) as max_grade,
                AVG(g.value) as average_grade,
                COUNT(DISTINCT g.student_id) as student_count,
                COUNT(g.id) as total_grades
            FROM grade g
            WHERE g.course_id = :course_id
        ';

        return $conn->executeQuery($sql, [
            'course_id' => $course->getId(),
        ])->fetchAllAssociative();
    }

    /**
     * Get distribution of grades in a course
     */
    public function getGradeDistribution(Course $course): array
    {
        $conn = $this->em->getConnection();

        $sql = '
            SELECT 
                CASE
                    WHEN g.value >= 18 THEN "Excellent (18-20)"
                    WHEN g.value >= 15 THEN "Very Good (15-17)"
                    WHEN g.value >= 12 THEN "Good (12-14)"
                    WHEN g.value >= 10 THEN "Average (10-11)"
                    ELSE "Poor (0-9)"
                END as grade_range,
                COUNT(g.id) as count,
                ROUND(COUNT(g.id) * 100.0 / (SELECT COUNT(*) FROM grade WHERE course_id = :course_id), 2) as percentage
            FROM grade g
            WHERE g.course_id = :course_id
            GROUP BY grade_range
            ORDER BY CASE
                WHEN g.value >= 18 THEN 1
                WHEN g.value >= 15 THEN 2
                WHEN g.value >= 12 THEN 3
                WHEN g.value >= 10 THEN 4
                ELSE 5
            END
        ';

        return $conn->executeQuery($sql, [
            'course_id' => $course->getId(),
        ])->fetchAllAssociative();
    }

    /**
     * Get student's ranking in a course
     */
    public function getStudentRankingInCourse(User $student, Course $course): array
    {
        $conn = $this->em->getConnection();

        $sql = '
            SELECT 
                @rank := @rank + 1 as rank,
                u.id as student_id,
                u.name,
                SUM(g.value * g.coefficient) / SUM(g.coefficient) as average
            FROM (
                SELECT 
                    u.id,
                    u.name,
                    SUM(g.value * g.coefficient) / SUM(g.coefficient) as average
                FROM user u
                LEFT JOIN grade g ON u.id = g.student_id AND g.course_id = :course_id
                LEFT JOIN enrollment e ON u.id = e.student_id AND e.course_id = :course_id
                WHERE e.id IS NOT NULL
                GROUP BY u.id, u.name
                ORDER BY average DESC
            ) as ranked_students,
            (SELECT @rank := 0) r
            WHERE u.id = :student_id
        ';

        return $conn->executeQuery($sql, [
            'course_id' => $course->getId(),
            'student_id' => $student->getId(),
        ])->fetchAllAssociative();
    }
}

