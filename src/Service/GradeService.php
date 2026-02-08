<?php

namespace App\Service;

use App\Entity\Course;
use App\Entity\Grade;
use App\Entity\User;
use App\Repository\GradeRepository;
use Doctrine\ORM\EntityManagerInterface;

class GradeService
{
    private const GRADE_MIN = 0;
    private const GRADE_MAX = 20;
    private const VALID_TYPES = ['exam', 'assignment', 'participation', 'project'];

    public function __construct(
        private EntityManagerInterface $em,
        private GradeRepository $gradeRepository,
    ) {
    }

    /**
     * Add a grade for a student.
     */
    public function addGrade(User $student, Course $course, float $value, string $type = 'exam', int $coefficient = 1): Grade
    {
        $this->validateGradeValue($value);
        $this->validateGradeType($type);
        $this->validateCoefficient($coefficient);

        $grade = new Grade();
        $grade->setStudent($student);
        $grade->setCourse($course);
        $grade->setValue($value);
        $grade->setType($type);
        $grade->setCoefficient($coefficient);

        $this->em->persist($grade);
        $this->em->flush();

        return $grade;
    }

    /**
     * Update a grade.
     */
    public function updateGrade(Grade $grade, float $value, ?string $type = null, ?int $coefficient = null): void
    {
        $this->validateGradeValue($value);
        if (null !== $type) {
            $this->validateGradeType($type);
        }
        if (null !== $coefficient) {
            $this->validateCoefficient($coefficient);
        }

        $grade->setValue($value);

        if (null !== $type) {
            $grade->setType($type);
        }

        if (null !== $coefficient) {
            $grade->setCoefficient($coefficient);
        }

        $this->em->flush();
    }

    /**
     * Delete a grade.
     */
    public function deleteGrade(Grade $grade): void
    {
        $this->em->remove($grade);
        $this->em->flush();
    }

    /**
     * Get all grades for a student in a course.
     */
    public function getGradesByStudentAndCourse(User $student, Course $course): array
    {
        return $this->gradeRepository->findByStudentAndCourse($student, $course);
    }

    /**
     * Get all grades in a course.
     */
    public function getGradesByCourse(Course $course): array
    {
        return $this->gradeRepository->findByCourse($course);
    }

    /**
     * Get average grade for a student in a course.
     */
    public function getAverageGrade(User $student, Course $course): ?float
    {
        return $this->gradeRepository->findAverageByStudentAndCourse($student, $course);
    }

    /**
     * Get all grades for a student.
     */
    public function getGradesByStudent(User $student): array
    {
        return $this->gradeRepository->findByStudent($student);
    }

    /**
     * Check if student has any grades in a course.
     */
    public function hasGradesInCourse(User $student, Course $course): bool
    {
        return $this->gradeRepository->countByStudentAndCourse($student, $course) > 0;
    }

    /**
     * Get grades by type in a course.
     */
    public function getGradesByType(Course $course, string $type): array
    {
        $this->validateGradeType($type);

        return $this->gradeRepository->findByCourseAndType($course, $type);
    }

    /**
     * Validate grade value.
     */
    private function validateGradeValue(float $value): void
    {
        if ($value < self::GRADE_MIN || $value > self::GRADE_MAX) {
            throw new \InvalidArgumentException(sprintf('Grade must be between %d and %d', self::GRADE_MIN, self::GRADE_MAX));
        }
    }

    /**
     * Validate grade type.
     */
    private function validateGradeType(string $type): void
    {
        if (!in_array($type, self::VALID_TYPES, true)) {
            throw new \InvalidArgumentException(sprintf('Grade type must be one of: %s', implode(', ', self::VALID_TYPES)));
        }
    }

    /**
     * Validate coefficient.
     */
    private function validateCoefficient(int $coefficient): void
    {
        if ($coefficient < 1) {
            throw new \InvalidArgumentException('Coefficient must be at least 1');
        }
    }
}
