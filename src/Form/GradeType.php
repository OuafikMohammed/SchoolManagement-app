<?php

namespace App\Form;

use App\Entity\Course;
use App\Entity\Grade;
use App\Entity\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Choice;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Positive;
use Symfony\Component\Validator\Constraints\Range;

class GradeType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('value', NumberType::class, [
                'label' => 'Grade Value (0-20)',
                'help' => 'Enter the grade between 0 and 20',
                'constraints' => [
                    new NotBlank(['message' => 'Grade value is required']),
                    new Range([
                        'min' => 0,
                        'max' => 20,
                        'notInRangeMessage' => 'Grade must be between 0 and 20',
                    ]),
                ],
                'attr' => [
                    'class' => 'form-control',
                    'min' => '0',
                    'max' => '20',
                    'step' => '0.5',
                ],
            ])
            ->add('type', ChoiceType::class, [
                'label' => 'Grade Type',
                'choices' => [
                    'Exam' => 'exam',
                    'Assignment' => 'assignment',
                    'Participation' => 'participation',
                    'Project' => 'project',
                ],
                'help' => 'Select the type of grade assessment',
                'constraints' => [
                    new NotBlank(['message' => 'Grade type is required']),
                    new Choice([
                        'choices' => ['exam', 'assignment', 'participation', 'project'],
                        'message' => 'Invalid grade type selected',
                    ]),
                ],
                'attr' => [
                    'class' => 'form-control',
                ],
            ])
            ->add('coefficient', IntegerType::class, [
                'label' => 'Weight (Coefficient)',
                'help' => 'Higher coefficient = more weight in average calculation',
                'data' => 1,
                'constraints' => [
                    new NotBlank(['message' => 'Coefficient is required']),
                    new Positive(['message' => 'Coefficient must be positive']),
                ],
                'attr' => [
                    'class' => 'form-control',
                    'min' => '1',
                ],
            ])
            ->add('student', EntityType::class, [
                'class' => User::class,
                'choice_label' => function (User $user) {
                    return $user->getName() ?: $user->getEmail();
                },
                'label' => 'Student',
                'help' => 'Select the student to grade',
                'placeholder' => 'Choose a student',
                'constraints' => [
                    new NotBlank(['message' => 'Student is required']),
                ],
                'attr' => [
                    'class' => 'form-control',
                ],
                'query_builder' => function ($repo) {
                    return $repo->createQueryBuilder('u')
                        ->where('u.roles NOT LIKE :admin')
                        ->setParameter('admin', '%ROLE_ADMIN%')
                        ->orderBy('u.name', 'ASC');
                },
            ])
            ->add('course', EntityType::class, [
                'class' => Course::class,
                'choice_label' => 'title',
                'label' => 'Course',
                'help' => 'Select the course for this grade',
                'placeholder' => 'Choose a course',
                'constraints' => [
                    new NotBlank(['message' => 'Course is required']),
                ],
                'attr' => [
                    'class' => 'form-control',
                ],
                'query_builder' => function ($repo) {
                    return $repo->createQueryBuilder('c')
                        ->orderBy('c.title', 'ASC');
                },
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Grade::class,
            'translation_domain' => 'forms',
        ]);
    }
}
