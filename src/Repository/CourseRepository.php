<?php

namespace App\Repository;

use App\Entity\Course;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Course>
 */
class CourseRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Course::class);
    }

    /**
     * Find all courses for a given student (courses they're enrolled in)
     */
    public function findCoursesForStudent(User $student): array
    {
        return $this->createQueryBuilder('c')
            ->join('c.enrollments', 'e')
            ->join('e.student', 's')
            ->andWhere('s = :student')
            ->setParameter('student', $student)
            ->orderBy('c.title', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Find available courses for a student (not yet enrolled)
     */
    public function findAvailableForStudent(User $student): array
    {
        return $this->createQueryBuilder('c')
            ->leftJoin('c.enrollments', 'e', 'WITH', 'e.student = :student')
            ->andWhere('e.id IS NULL')
            ->setParameter('student', $student)
            ->orderBy('c.title', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Find all courses taught by a teacher
     */
    public function findByTeacher(User $teacher): array
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.teacher = :teacher')
            ->setParameter('teacher', $teacher)
            ->orderBy('c.title', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
