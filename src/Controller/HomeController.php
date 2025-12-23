<?php

namespace App\Controller;

use App\Repository\CourseRepository;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class HomeController extends AbstractController
{
    #[Route('/', name: 'home')]
    public function index(CourseRepository $courseRepository, UserRepository $userRepository): Response
    {
        if ($this->getUser()) {
            // Redirect authenticated users based on role
            if ($this->isGranted('ROLE_TEACHER')) {
                return $this->redirectToRoute('app_teacher_dashboard');
            }
            if ($this->isGranted('ROLE_STUDENT')) {
                return $this->redirectToRoute('app_student_dashboard');
            }
        }

        // Show homepage for anonymous users
        $stats = [
            'total_courses' => $courseRepository->count([]),
            'total_users' => $userRepository->count([]),
            'recent_courses' => $courseRepository->findBy([], ['id' => 'DESC'], 6),
        ];

        return $this->render('home/index.html.twig', $stats);
    }
}
