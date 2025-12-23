<?php

namespace App\Controller\Student;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/student/grades', name: 'app_student_')]
#[IsGranted('ROLE_STUDENT')]
class GradeController extends AbstractController
{
    #[Route('', name: 'grades')]
    public function myGrades(): Response
    {
        return $this->render('student/grade/my_grades.html.twig', [
            'enrollments' => $this->getUser()->getEnrollments(),
        ]);
    }
}
