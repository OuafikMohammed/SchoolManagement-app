<?php

namespace App\Controller\Student;

use App\Entity\Course;
use App\Repository\CourseRepository;
use App\Service\EnrollmentService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/student/enrollments')]
#[IsGranted('ROLE_STUDENT')]
class EnrollmentController extends AbstractController
{
    #[Route('/available', name: 'app_enrollment_available', methods: ['GET'])]
    public function available(CourseRepository $courseRepository): Response
    {
        $availableCourses = $courseRepository->findAvailableForStudent($this->getUser());

        return $this->render('student/enrollment/available.html.twig', [
            'courses' => $availableCourses,
        ]);
    }

    #[Route('/{courseId}/enroll', name: 'app_enrollment_enroll', methods: ['POST'])]
    public function enroll(
        int $courseId,
        CourseRepository $courseRepository,
        EnrollmentService $enrollmentService,
        Request $request
    ): Response {
        if (!$this->isCsrfTokenValid('enroll'.$courseId, $request->request->get('_token'))) {
            $this->addFlash('error', 'Invalid request');
            return $this->redirectToRoute('app_enrollment_available');
        }

        $course = $courseRepository->find($courseId);
        if (!$course) {
            throw $this->createNotFoundException('Course not found');
        }

        try {
            $enrollmentService->enrollStudent($this->getUser(), $course);
            $this->addFlash('success', 'Successfully enrolled in '.$course->getTitle());
        } catch (\Exception $e) {
            $this->addFlash('error', $e->getMessage());
        }

        return $this->redirectToRoute('app_enrollment_available');
    }

    #[Route('/{courseId}/drop', name: 'app_enrollment_drop', methods: ['POST'])]
    public function drop(
        int $courseId,
        CourseRepository $courseRepository,
        EnrollmentService $enrollmentService,
        Request $request
    ): Response {
        if (!$this->isCsrfTokenValid('drop'.$courseId, $request->request->get('_token'))) {
            $this->addFlash('error', 'Invalid request');
            return $this->redirectToRoute('app_student_dashboard');
        }

        $course = $courseRepository->find($courseId);
        if (!$course) {
            throw $this->createNotFoundException('Course not found');
        }

        try {
            $enrollmentService->dropStudent($this->getUser(), $course);
            $this->addFlash('success', 'Successfully dropped from '.$course->getTitle());
        } catch (\Exception $e) {
            $this->addFlash('error', $e->getMessage());
        }

        return $this->redirectToRoute('app_student_dashboard');
    }
}
