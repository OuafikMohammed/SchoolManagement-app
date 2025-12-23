<?php

namespace App\Form;

use App\Entity\Grade;
use App\Entity\User;
use App\Entity\Course;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Range;

class GradeType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('value', NumberType::class, [
                'label' => 'Grade (0-20)',
                'constraints' => [
                    new Range(['min' => 0, 'max' => 20]),
                ],
            ])
            ->add('type', ChoiceType::class, [
                'choices' => [
                    'Exam' => 'exam',
                    'Homework' => 'homework',
                    'Participation' => 'participation',
                    'Project' => 'project',
                ],
            ])
            ->add('coefficient', IntegerType::class, [
                'label' => 'Weight (coefficient)',
                'data' => 1,
            ])
            ->add('student', EntityType::class, [
                'class' => User::class,
                'choice_label' => 'email',
            ])
            ->add('course', EntityType::class, [
                'class' => Course::class,
                'choice_label' => 'title',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Grade::class,
        ]);
    }
}
