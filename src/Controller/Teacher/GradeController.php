<?php

namespace App\Controller\Teacher;

use App\Entity\Course;
use App\Entity\Grade;
use App\Form\GradeType;
use App\Repository\GradeRepository;
use App\Service\GradeService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/teacher/grades')]
#[IsGranted('ROLE_TEACHER')]
class GradeController extends AbstractController
{
    #[Route('', name: 'app_grade_index', methods: ['GET'])]
    public function index(GradeRepository $gradeRepository): Response
    {
        // Show all grades for courses taught by this teacher
        $courses = $this->getUser()->getCourses();
        $grades = [];

        foreach ($courses as $course) {
            $grades = array_merge($grades, $gradeRepository->findByCourse($course));
        }

        return $this->render('teacher/grade/index.html.twig', [
            'grades' => $grades,
        ]);
    }

    #[Route('/course/{courseId}/add', name: 'app_grade_add', methods: ['GET', 'POST'])]
    public function add(
        int $courseId,
        Request $request,
        EntityManagerInterface $em,
        GradeService $gradeService
    ): Response {
        $course = $em->getRepository(Course::class)->find($courseId);

        if (!$course || $course->getTeacher() !== $this->getUser()) {
            throw $this->createAccessDeniedException('You can only add grades to your own courses');
        }

        $grade = new Grade();
        $grade->setCourse($course);

        $form = $this->createForm(GradeType::class, $grade);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $gradeService->addGrade(
                $grade->getStudent(),
                $grade->getCourse(),
                $grade->getValue(),
                $grade->getType(),
                $grade->getCoefficient()
            );

            $this->addFlash('success', 'Grade added successfully!');
            return $this->redirectToRoute('app_grade_index');
        }

        return $this->render('teacher/grade/add.html.twig', [
            'form' => $form->createView(),
            'course' => $course,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_grade_edit', methods: ['GET', 'POST'])]
    public function edit(
        Grade $grade,
        Request $request,
        GradeService $gradeService
    ): Response {
        $this->denyAccessUnlessGranted('EDIT', $grade);

        $form = $this->createForm(GradeType::class, $grade);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $gradeService->updateGrade(
                $grade,
                $grade->getValue(),
                $grade->getType(),
                $grade->getCoefficient()
            );

            $this->addFlash('success', 'Grade updated successfully!');
            return $this->redirectToRoute('app_grade_index');
        }

        return $this->render('teacher/grade/edit.html.twig', [
            'form' => $form->createView(),
            'grade' => $grade,
        ]);
    }

    #[Route('/{id}/delete', name: 'app_grade_delete', methods: ['POST'])]
    public function delete(
        Grade $grade,
        Request $request,
        GradeService $gradeService
    ): Response {
        $this->denyAccessUnlessGranted('DELETE', $grade);

        if ($this->isCsrfTokenValid('delete'.$grade->getId(), $request->request->get('_token'))) {
            $gradeService->deleteGrade($grade);
            $this->addFlash('success', 'Grade deleted successfully!');
        }

        return $this->redirectToRoute('app_grade_index');
    }
}
