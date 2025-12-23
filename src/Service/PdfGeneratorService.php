<?php

namespace App\Service;

use App\Entity\Course;
use App\Entity\User;
use Dompdf\Dompdf;
use Twig\Environment;

class PdfGeneratorService
{
    public function __construct(
        private Environment $twig,
        private StatisticService $statisticService
    ) {}

    /**
     * Generate a student bulletin (grade report) PDF
     */
    public function generateBulletin(User $student, Course $course): string
    {
        $grades = $course->getGrades()->filter(fn(Grade $g) => $g->getStudent() === $student);
        $average = $this->statisticService->calculateAverageForStudentInCourse($student, $course);

        $html = $this->twig->render('pdf/bulletin.html.twig', [
            'student' => $student,
            'course' => $course,
            'grades' => $grades,
            'average' => $average,
            'generatedAt' => new \DateTime(),
        ]);

        return $this->renderToPdf($html, $student->getName() . '_' . $course->getTitle());
    }

    /**
     * Generate a course report (teacher view) PDF
     */
    public function generateCourseReport(Course $course): string
    {
        $students = $course->getEnrollments();
        $gradeDistribution = [];

        foreach ($students as $enrollment) {
            $student = $enrollment->getStudent();
            $average = $this->statisticService->calculateAverageForStudentInCourse($student, $course);
            $gradeDistribution[] = [
                'student' => $student,
                'average' => $average,
                'gradeCount' => $course->getGrades()->filter(fn(Grade $g) => $g->getStudent() === $student)->count(),
            ];
        }

        $html = $this->twig->render('pdf/course_report.html.twig', [
            'course' => $course,
            'gradeDistribution' => $gradeDistribution,
            'generatedAt' => new \DateTime(),
        ]);

        return $this->renderToPdf($html, $course->getTitle() . '_Report');
    }

    /**
     * Convert HTML to PDF
     */
    private function renderToPdf(string $html, string $filename): string
    {
        $dompdf = new Dompdf();
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        return $dompdf->output();
    }
}
