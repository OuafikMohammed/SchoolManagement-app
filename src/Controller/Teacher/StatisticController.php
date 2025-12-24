<?php

namespace App\Controller\Teacher;

use App\Entity\Course;
use App\Repository\GradeRepository;
use App\Service\StatisticService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Doctrine\ORM\EntityManagerInterface;

#[Route('/teacher/statistics')]
#[IsGranted('ROLE_TEACHER')]
class StatisticController extends AbstractController
{
    public function __construct(
        private StatisticService $statisticService,
        private GradeRepository $gradeRepository,
        private EntityManagerInterface $em,
    ) {
    }

    /**
     * Display statistics overview for all teacher's courses
     */
    #[Route('', name: 'app_statistic_index')]
    public function index(): Response
    {
        $user = $this->getUser();
        $courses = $user->getCourses();
        $courseStats = [];

        foreach ($courses as $course) {
            $ranking = $this->statisticService->getCourseRanking($course);
            $statistics = $this->statisticService->getClassStatistics($course);
            $distribution = $this->statisticService->getGradeDistribution($course);
            
            $courseStats[] = [
                'course' => $course,
                'ranking' => $ranking,
                'statistics' => $statistics,
                'distribution' => $distribution,
                'studentCount' => count($course->getEnrollments()),
                'gradeCount' => count($this->gradeRepository->findByCourse($course)),
            ];
        }

        // Sort by grade count
        usort($courseStats, fn($a, $b) => $b['gradeCount'] <=> $a['gradeCount']);

        return $this->render('teacher/statistic/index.html.twig', [
            'courseStats' => $courseStats,
        ]);
    }

    /**
     * Display detailed statistics for a specific course
     */
    #[Route('/course/{courseId}', name: 'app_statistic_course')]
    public function course(int $courseId): Response
    {
        $course = $this->em->getRepository(Course::class)->find($courseId);

        if (!$course || $course->getTeacher() !== $this->getUser()) {
            throw $this->createAccessDeniedException();
        }

        $ranking = $this->statisticService->getCourseRanking($course);
        $statistics = $this->statisticService->getClassStatistics($course);
        $distribution = $this->statisticService->getGradeDistribution($course);
        $grades = $this->gradeRepository->findByCourse($course);

        return $this->render('teacher/statistic/course.html.twig', [
            'course' => $course,
            'ranking' => $ranking,
            'statistics' => $statistics,
            'distribution' => $distribution,
            'grades' => $grades,
        ]);
    }

    /**
     * Display detailed statistics for a specific student
     */
    #[Route('/student/{studentId}/course/{courseId}', name: 'app_statistic_student_course')]
    public function studentCourse(int $studentId, int $courseId): Response
    {
        $student = $this->em->getRepository(\App\Entity\User::class)->find($studentId);
        $course = $this->em->getRepository(Course::class)->find($courseId);

        if (!$course || $course->getTeacher() !== $this->getUser() || !$student) {
            throw $this->createAccessDeniedException();
        }

        $progress = $this->statisticService->getStudentProgress($student, $course);
        $averagesByType = $this->statisticService->getAveragesByType($student, $course);
        $rankingPosition = $this->statisticService->getStudentRankingPosition($student, $course);
        $grades = $this->gradeRepository->findByStudentAndCourse($student, $course);

        return $this->render('teacher/statistic/student.html.twig', [
            'student' => $student,
            'course' => $course,
            'progress' => $progress,
            'averagesByType' => $averagesByType,
            'rankingPosition' => $rankingPosition,
            'grades' => $grades,
        ]);
    }

    /**
     * Export statistics as JSON
     */
    #[Route('/course/{courseId}/export', name: 'app_statistic_export')]
    public function export(int $courseId): Response
    {
        $course = $this->em->getRepository(Course::class)->find($courseId);

        if (!$course || $course->getTeacher() !== $this->getUser()) {
            throw $this->createAccessDeniedException();
        }

        $ranking = $this->statisticService->getCourseRanking($course);
        $statistics = $this->statisticService->getClassStatistics($course);
        $distribution = $this->statisticService->getGradeDistribution($course);

        $data = [
            'course' => [
                'id' => $course->getId(),
                'title' => $course->getTitle(),
                'description' => $course->getDescription(),
            ],
            'statistics' => $statistics,
            'distribution' => $distribution,
            'ranking' => $ranking,
            'exportedAt' => (new \DateTime())->format('Y-m-d H:i:s'),
        ];

        $response = new Response(json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        $response->headers->set('Content-Type', 'application/json');
        $response->headers->set('Content-Disposition', 'attachment; filename="statistics_'.$course->getId().'.json"');

        return $response;
    }
}

