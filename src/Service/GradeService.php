<?php

namespace App\Service;

use App\Entity\Course;
use App\Entity\Grade;
use App\Entity\User;
use App\Repository\GradeRepository;
use Doctrine\ORM\EntityManagerInterface;

class GradeService
{
    public function __construct(
        private EntityManagerInterface $em,
        private GradeRepository $gradeRepository,
    ) {
    }

    /**
     * Add a grade for a student
     */
    public function addGrade(User $student, Course $course, float $value, string $type = 'exam', int $coefficient = 1): Grade
    {
        if ($value < 0 || $value > 20) {
            throw new \Exception('Grade must be between 0 and 20');
        }

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
     * Update a grade
     */
    public function updateGrade(Grade $grade, float $value, string $type = null, int $coefficient = null): void
    {
        if ($value < 0 || $value > 20) {
            throw new \Exception('Grade must be between 0 and 20');
        }

        $grade->setValue($value);

        if ($type) {
            $grade->setType($type);
        }

        if ($coefficient) {
            $grade->setCoefficient($coefficient);
        }

        $this->em->flush();
    }

    /**
     * Delete a grade
     */
    public function deleteGrade(Grade $grade): void
    {
        $this->em->remove($grade);
        $this->em->flush();
    }
}
