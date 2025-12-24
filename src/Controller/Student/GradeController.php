<?php

namespace App\Controller\Student;

use App\Entity\Course;
use App\Repository\GradeRepository;
use App\Service\StatisticService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Doctrine\ORM\EntityManagerInterface;

#[Route('/student/grades', name: 'app_student_')]
#[IsGranted('ROLE_STUDENT')]
class GradeController extends AbstractController
{
    public function __construct(
        private GradeRepository $gradeRepository,
        private StatisticService $statisticService,
        private EntityManagerInterface $em,
    ) {
    }

    /**
     * View all my grades and statistics
     */
    #[Route('', name: 'grades')]
    public function myGrades(): Response
    {
        $student = $this->getUser();
        assert($student instanceof \App\Entity\User);
        $enrollments = $student->getEnrollments();
        $courseGrades = [];

        foreach ($enrollments as $enrollment) {
            $course = $enrollment->getCourse();
            $grades = $this->gradeRepository->findByStudentAndCourse($student, $course);
            $average = $this->statisticService->calculateAverageForStudentInCourse($student, $course);
            $ranking = $this->statisticService->getStudentRankingPosition($student, $course);

            $courseGrades[] = [
                'course' => $course,
                'grades' => $grades,
                'average' => $average,
                'ranking' => $ranking,
                'gradeCount' => count($grades),
            ];
        }

        // Sort by average grade (highest first)
        usort($courseGrades, fn($a, $b) => $b['average'] <=> $a['average']);

        return $this->render('student/grade/my_grades.html.twig', [
            'enrollments' => $enrollments,
            'courseGrades' => $courseGrades,
            'overallAverage' => $this->statisticService->calculateOverallAverage($student),
        ]);
    }

    /**
     * View grades for a specific course
     */
    #[Route('/course/{courseId}', name: 'grades_course')]
    public function courseGrades(int $courseId): Response
    {
        $student = $this->getUser();
        $course = $this->em->getRepository(Course::class)->find($courseId);

        if (!$course) {
            throw $this->createNotFoundException('Course not found');
        }

        // Check if student is enrolled in this course
        $enrollment = $this->em->getRepository(\App\Entity\Enrollment::class)
            ->findEnrollment($student, $course);

        if (!$enrollment) {
            throw $this->createAccessDeniedException('You are not enrolled in this course');
        }

        $grades = $this->gradeRepository->findByStudentAndCourse($student, $course);
        $average = $this->statisticService->calculateAverageForStudentInCourse($student, $course);
        $averagesByType = $this->statisticService->getAveragesByType($student, $course);
        $progress = $this->statisticService->getStudentProgress($student, $course);
        $ranking = $this->statisticService->getStudentRankingPosition($student, $course);

        return $this->render('student/grade/course_grades.html.twig', [
            'course' => $course,
            'grades' => $grades,
            'average' => $average,
            'averagesByType' => $averagesByType,
            'progress' => $progress,
            'ranking' => $ranking,
        ]);
    }

    /**
     * View overall statistics
     */
    #[Route('/statistics', name: 'statistics')]
    public function statistics(): Response
    {
        $student = $this->getUser();
        assert($student instanceof \App\Entity\User);
        $enrollments = $student->getEnrollments();

        $courseStats = [];
        foreach ($enrollments as $enrollment) {
            $course = $enrollment->getCourse();
            $progress = $this->statisticService->getStudentProgress($student, $course);
            $average = $this->statisticService->calculateAverageForStudentInCourse($student, $course);
            $ranking = $this->statisticService->getStudentRankingPosition($student, $course);

            $courseStats[] = [
                'course' => $course,
                'average' => $average,
                'progress' => $progress,
                'ranking' => $ranking,
            ];
        }

        // Sort by average (highest first)
        usort($courseStats, fn($a, $b) => $b['average'] <=> $a['average']);

        return $this->render('student/statistic/my_stats.html.twig', [
            'courseStats' => $courseStats,
            'overallAverage' => $this->statisticService->calculateOverallAverage($student),
            'enrollmentCount' => count($enrollments),
        ]);
    }
}

