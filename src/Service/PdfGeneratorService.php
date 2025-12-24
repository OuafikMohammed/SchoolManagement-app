<?php

namespace App\Service;

use App\Entity\Course;
use App\Entity\Grade;
use App\Entity\User;
use App\Repository\StatisticRepository;
use Dompdf\Dompdf;
use Dompdf\Options;
use Twig\Environment;

class PdfGeneratorService
{
    public function __construct(
        private Environment $twig,
        private StatisticRepository $statisticRepository
    ) {
    }

    /**
     * Generate a student bulletin (grade report) PDF
     */
    public function generateBulletin(User $student, Course $course): string
    {
        $grades = $course->getGrades()->filter(fn(Grade $g) => $g->getStudent() === $student);
        $average = $this->statisticRepository->calculateAverageGrade($student, $course);
        $rankedStudents = $this->statisticRepository->getRankedStudentsByCourse($course);

        // Find student's rank
        $rank = null;
        foreach ($rankedStudents as $index => $rankedStudent) {
            if ($rankedStudent['student_id'] === $student->getId()) {
                $rank = $index + 1;
                break;
            }
        }

        $html = $this->twig->render('pdf/bulletin.html.twig', [
            'student' => $student,
            'course' => $course,
            'grades' => $grades,
            'average' => $average,
            'rank' => $rank,
            'totalStudents' => count($rankedStudents),
            'generatedAt' => new \DateTime(),
        ]);

        return $this->renderToPdf($html, $student->getName() . '_' . $course->getTitle());
    }

    /**
     * Generate a course report (teacher view) PDF
     */
    public function generateCourseReport(Course $course): string
    {
        $enrollments = $course->getEnrollments();
        $gradeDistribution = [];

        foreach ($enrollments as $enrollment) {
            $student = $enrollment->getStudent();
            $average = $this->statisticRepository->calculateAverageGrade($student, $course);
            $gradeDistribution[] = [
                'student' => $student,
                'average' => $average,
                'gradeCount' => $course->getGrades()->filter(fn(Grade $g) => $g->getStudent() === $student)->count(),
            ];
        }

        // Sort by average grade (descending)
        usort($gradeDistribution, function ($a, $b) {
            $avgA = $a['average'] ?? 0;
            $avgB = $b['average'] ?? 0;
            return $avgB <=> $avgA;
        });

        $html = $this->twig->render('pdf/course_report.html.twig', [
            'course' => $course,
            'gradeDistribution' => $gradeDistribution,
            'totalStudents' => count($enrollments),
            'generatedAt' => new \DateTime(),
        ]);

        return $this->renderToPdf($html, $course->getTitle() . '_Report');
    }

    /**
     * Convert HTML to PDF
     */
    private function renderToPdf(string $html, string $filename): string
    {
        $options = new Options();
        $options->set([
            'isRemoteEnabled' => false,
            'isHtml5ParserEnabled' => true,
            'defaultFont' => 'Helvetica',
            'dpi' => 96,
        ]);

        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        return $dompdf->output();
    }
}
