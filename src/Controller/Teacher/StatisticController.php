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
    #[Route('', name: 'app_statistic_index')]
    public function index(
        StatisticService $statisticService,
        EntityManagerInterface $em
    ): Response {
        $courses = $this->getUser()->getCourses();
        $courseStats = [];

        foreach ($courses as $course) {
            $ranking = $statisticService->getCourseRanking($course);
            $courseStats[] = [
                'course' => $course,
                'ranking' => $ranking,
                'studentCount' => count($course->getEnrollments()),
            ];
        }

        return $this->render('teacher/statistic/index.html.twig', [
            'courseStats' => $courseStats,
        ]);
    }

    #[Route('/course/{courseId}', name: 'app_statistic_course')]
    public function course(
        int $courseId,
        StatisticService $statisticService,
        EntityManagerInterface $em
    ): Response {
        $course = $em->getRepository(Course::class)->find($courseId);

        if (!$course || $course->getTeacher() !== $this->getUser()) {
            throw $this->createAccessDeniedException();
        }

        $ranking = $statisticService->getCourseRanking($course);

        return $this->render('teacher/statistic/course.html.twig', [
            'course' => $course,
            'ranking' => $ranking,
        ]);
    }
}
