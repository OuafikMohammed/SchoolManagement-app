<?php

namespace App\Service;

use App\Entity\Course;
use App\Entity\Enrollment;
use App\Entity\User;
use App\Repository\EnrollmentRepository;
use Doctrine\ORM\EntityManagerInterface;

class EnrollmentService
{
    public function __construct(
        private EntityManagerInterface $em,
        private EnrollmentRepository $enrollmentRepository,
    ) {
    }

    /**
     * Enroll a student in a course.
     */
    public function enrollStudent(User $student, Course $course): Enrollment
    {
        // Check if already enrolled
        if ($this->enrollmentRepository->findEnrollment($student, $course)) {
            throw new \Exception('Student is already enrolled in this course');
        }

        $enrollment = new Enrollment();
        $enrollment->setStudent($student);
        $enrollment->setCourse($course);
        $enrollment->setEnrolledAt(new \DateTime());

        $this->em->persist($enrollment);
        $this->em->flush();

        return $enrollment;
    }

    /**
     * Drop a student from a course.
     */
    public function dropStudent(User $student, Course $course): void
    {
        $enrollment = $this->enrollmentRepository->findEnrollment($student, $course);

        if (!$enrollment) {
            throw new \Exception('Student is not enrolled in this course');
        }

        $this->em->remove($enrollment);
        $this->em->flush();
    }

    /**
     * Check if student is enrolled in course.
     */
    public function isEnrolled(User $student, Course $course): bool
    {
        return null !== $this->enrollmentRepository->findEnrollment($student, $course);
    }
}
