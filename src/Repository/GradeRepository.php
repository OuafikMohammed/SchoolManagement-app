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

    /**
     * Find grades by type in a course
     */
    public function findByCourseAndType(Course $course, string $type): array
    {
        return $this->createQueryBuilder('g')
            ->andWhere('g.course = :course')
            ->andWhere('g.type = :type')
            ->setParameter('course', $course)
            ->setParameter('type', $type)
            ->orderBy('g.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Find average grade for a student in a course with weighted coefficient
     */
    public function findAverageByStudentAndCourse(User $student, Course $course): ?float
    {
        $result = $this->createQueryBuilder('g')
            ->select('SUM(g.value * g.coefficient) / SUM(g.coefficient) as average')
            ->andWhere('g.student = :student')
            ->andWhere('g.course = :course')
            ->setParameter('student', $student)
            ->setParameter('course', $course)
            ->getQuery()
            ->getOneOrNullResult();

        return $result['average'] !== null ? (float) $result['average'] : null;
    }

    /**
     * Find all students in a course with their average grades
     */
    public function findStudentsWithAveragesByCourse(Course $course): array
    {
        return $this->createQueryBuilder('g')
            ->select('g.student, AVG(g.value * g.coefficient / SUM(g.coefficient)) as average')
            ->andWhere('g.course = :course')
            ->setParameter('course', $course)
            ->groupBy('g.student')
            ->orderBy('average', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Count total grades for a student in a course
     */
    public function countByStudentAndCourse(User $student, Course $course): int
    {
        return (int) $this->createQueryBuilder('g')
            ->select('COUNT(g.id)')
            ->andWhere('g.student = :student')
            ->andWhere('g.course = :course')
            ->setParameter('student', $student)
            ->setParameter('course', $course)
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * Find all grades in a course grouped by student
     */
    public function findByCourseGroupedByStudent(Course $course): array
    {
        return $this->createQueryBuilder('g')
            ->andWhere('g.course = :course')
            ->setParameter('course', $course)
            ->orderBy('g.student', 'ASC')
            ->addOrderBy('g.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Delete all grades for a student in a course
     */
    public function deleteByStudentAndCourse(User $student, Course $course): int
    {
        return $this->createQueryBuilder('g')
            ->delete()
            ->andWhere('g.student = :student')
            ->andWhere('g.course = :course')
            ->setParameter('student', $student)
            ->setParameter('course', $course)
            ->getQuery()
            ->execute();
    }
}
