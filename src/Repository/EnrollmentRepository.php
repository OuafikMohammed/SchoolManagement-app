<?php

namespace App\Repository;

use App\Entity\Course;
use App\Entity\Enrollment;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Enrollment>
 */
class EnrollmentRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Enrollment::class);
    }

    /**
     * Find enrollment for a student in a course.
     */
    public function findEnrollment(User $student, Course $course): ?Enrollment
    {
        return $this->createQueryBuilder('e')
            ->andWhere('e.student = :student')
            ->andWhere('e.course = :course')
            ->setParameter('student', $student)
            ->setParameter('course', $course)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * Find all enrollments for a student.
     */
    public function findByStudent(User $student): array
    {
        return $this->createQueryBuilder('e')
            ->andWhere('e.student = :student')
            ->setParameter('student', $student)
            ->orderBy('e.enrolledAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Find all enrollments in a course.
     */
    public function findByCourse(Course $course): array
    {
        return $this->createQueryBuilder('e')
            ->andWhere('e.course = :course')
            ->setParameter('course', $course)
            ->orderBy('e.enrolledAt', 'DESC')
            ->getQuery()
            ->getResult();
    }
}
