<?php

namespace App\Controller\Shared;

use App\Repository\CourseRepository;
use App\Service\PdfGeneratorService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/pdf', name: 'pdf_')]
class PdfController extends AbstractController
{
    #[Route('/bulletin/{courseId}', name: 'student_bulletin')]
    #[IsGranted('ROLE_STUDENT')]
    public function studentBulletin(
        int $courseId,
        CourseRepository $courseRepository,
        PdfGeneratorService $pdfGenerator
    ): Response {
        $course = $courseRepository->find($courseId);
        if (!$course) {
            throw $this->createNotFoundException('Course not found');
        }

        // Check if student is enrolled in this course
        $isEnrolled = false;
        foreach ($course->getEnrollments() as $enrollment) {
            if ($enrollment->getStudent() === $this->getUser()) {
                $isEnrolled = true;
                break;
            }
        }

        if (!$isEnrolled) {
            throw $this->createAccessDeniedException('You are not enrolled in this course');
        }

        $pdf = $pdfGenerator->generateBulletin($this->getUser(), $course);

        return new Response($pdf, Response::HTTP_OK, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="bulletin.pdf"',
        ]);
    }

    #[Route('/course-report/{courseId}', name: 'course_report')]
    #[IsGranted('ROLE_TEACHER')]
    public function courseReport(
        int $courseId,
        CourseRepository $courseRepository,
        PdfGeneratorService $pdfGenerator
    ): Response {
        $course = $courseRepository->find($courseId);
        if (!$course) {
            throw $this->createNotFoundException('Course not found');
        }

        // Check if teacher owns this course
        if ($course->getTeacher() !== $this->getUser()) {
            throw $this->createAccessDeniedException('You do not own this course');
        }

        $pdf = $pdfGenerator->generateCourseReport($course);

        return new Response($pdf, Response::HTTP_OK, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="course_report.pdf"',
        ]);
    }

    #[Route('/course-report/{courseId}/view', name: 'course_report_view')]
    #[IsGranted('ROLE_TEACHER')]
    public function courseReportView(
        int $courseId,
        CourseRepository $courseRepository,
        PdfGeneratorService $pdfGenerator
    ): Response {
        $course = $courseRepository->find($courseId);
        if (!$course) {
            throw $this->createNotFoundException('Course not found');
        }

        // Check if teacher owns this course
        if ($course->getTeacher() !== $this->getUser()) {
            throw $this->createAccessDeniedException('You do not own this course');
        }

        $pdf = $pdfGenerator->generateCourseReport($course);

        return new Response($pdf, Response::HTTP_OK, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="course_report.pdf"',
        ]);
    }

    #[Route('/bulletin/{courseId}/view', name: 'student_bulletin_view')]
    #[IsGranted('ROLE_STUDENT')]
    public function studentBulletinView(
        int $courseId,
        CourseRepository $courseRepository,
        PdfGeneratorService $pdfGenerator
    ): Response {
        $course = $courseRepository->find($courseId);
        if (!$course) {
            throw $this->createNotFoundException('Course not found');
        }

        // Check if student is enrolled in this course
        $isEnrolled = false;
        foreach ($course->getEnrollments() as $enrollment) {
            if ($enrollment->getStudent() === $this->getUser()) {
                $isEnrolled = true;
                break;
            }
        }

        if (!$isEnrolled) {
            throw $this->createAccessDeniedException('You are not enrolled in this course');
        }

        $pdf = $pdfGenerator->generateBulletin($this->getUser(), $course);

        return new Response($pdf, Response::HTTP_OK, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="bulletin.pdf"',
        ]);
    }

    #[Route('/testing-dashboard', name: 'testing_dashboard')]
    public function testingDashboard(): Response
    {
        return $this->render('pdf/testing_dashboard.html.twig');
    }
}
