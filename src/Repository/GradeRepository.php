<?php

namespace App\Repository;

use App\Entity\Course;
use App\Entity\Grade;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Grade>
 */
class GradeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Grade::class);
    }

    /**
     * Find all grades for a student
     */
    public function findByStudent(User $student): array
    {
        return $this->createQueryBuilder('g')
            ->andWhere('g.student = :student')
            ->setParameter('student', $student)
            ->orderBy('g.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Find all grades in a course
     */
    public function findByCourse(Course $course): array
    {
        return $this->createQueryBuilder('g')
            ->andWhere('g.course = :course')
            ->setParameter('course', $course)
            ->orderBy('g.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Find grades for a student in a course
     */
    public function findByStudentAndCourse(User $student, Course $course): array
    {
        return $this->createQueryBuilder('g')
            ->andWhere('g.student = :student')
            ->andWhere('g.course = :course')
            ->setParameter('student', $student)
            ->setParameter('course', $course)
            ->orderBy('g.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }
}
