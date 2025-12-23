<?php

namespace App\Controller\Teacher;

use App\Repository\CourseRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/teacher')]
#[IsGranted('ROLE_TEACHER')]
class DashboardController extends AbstractController
{
    #[Route('/dashboard', name: 'app_teacher_dashboard')]
    public function index(CourseRepository $courseRepository): Response
    {
        $courses = $courseRepository->findByTeacher($this->getUser());

        return $this->render('teacher/dashboard.html.twig', [
            'courses' => $courses,
        ]);
    }
}
