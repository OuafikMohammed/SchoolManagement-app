<?php

namespace App\DataFixtures;

use App\Entity\Course;
use App\Entity\Enrollment;
use App\Entity\Grade;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory as FakerFactory;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    public function __construct(private UserPasswordHasherInterface $passwordHasher)
    {
    }

    public function load(ObjectManager $manager): void
    {
        $faker = FakerFactory::create();

        // Create teachers (8 teachers)
        $teachers = [];
        for ($i = 0; $i < 8; $i++) {
            $teacher = new User();
            $teacher->setEmail("teacher{$i}@school.test");
            $teacher->setName($faker->name());
            $teacher->setRoles(['ROLE_TEACHER']);
            $hashedPassword = $this->passwordHasher->hashPassword($teacher, 'password');
            $teacher->setPassword($hashedPassword);
            $manager->persist($teacher);
            $teachers[] = $teacher;
        }

        // Create students (50 students)
        $students = [];
        for ($i = 0; $i < 50; $i++) {
            $student = new User();
            $student->setEmail("student{$i}@school.test");
            $student->setName($faker->name());
            $student->setRoles(['ROLE_STUDENT']);
            $hashedPassword = $this->passwordHasher->hashPassword($student, 'password');
            $student->setPassword($hashedPassword);
            $manager->persist($student);
            $students[] = $student;
        }

        // Create admin
        $admin = new User();
        $admin->setEmail('admin@school.test');
        $admin->setName('Admin User');
        $admin->setRoles(['ROLE_ADMIN']);
        $hashedPassword = $this->passwordHasher->hashPassword($admin, 'password');
        $admin->setPassword($hashedPassword);
        $manager->persist($admin);

        $manager->flush();

        // Create courses
        $courses = [];
        $courseNames = [
            'Mathematics',
            'Physics',
            'Chemistry',
            'Biology',
            'History',
            'Geography',
            'English',
            'French',
            'Spanish',
            'Computer Science',
            'Art',
            'Physical Education',
            'Music',
            'Literature',
            'Economics',
            'Psychology',
            'Philosophy',
            'Statistics',
            'Algebra',
            'Geometry',
        ];

        foreach ($courseNames as $idx => $name) {
            $course = new Course();
            $course->setTitle($name);
            $course->setDescription($faker->paragraph());
            $course->setTeacher($teachers[$idx % count($teachers)]);
            $manager->persist($course);
            $courses[] = $course;
        }

        $manager->flush();

        // Create enrollments
        foreach ($students as $student) {
            $numCourses = $faker->numberBetween(3, 6);
            $enrolledCourses = $faker->randomElements($courses, $numCourses);

            foreach ($enrolledCourses as $course) {
                $enrollment = new Enrollment();
                $enrollment->setStudent($student);
                $enrollment->setCourse($course);
                $enrollment->setEnrolledAt(new \DateTime($faker->dateTimeBetween('-6 months')->format('Y-m-d H:i:s')));
                $manager->persist($enrollment);
            }
        }

        $manager->flush();

        // Create grades
        $gradeTypes = ['exam', 'homework', 'project', 'participation', 'quiz'];
        $enrollments = $manager->getRepository(Enrollment::class)->findAll();

        foreach ($enrollments as $enrollment) {
            $numGrades = $faker->numberBetween(4, 8);

            for ($i = 0; $i < $numGrades; $i++) {
                $grade = new Grade();
                $grade->setStudent($enrollment->getStudent());
                $grade->setCourse($enrollment->getCourse());
                $grade->setValue($faker->numberBetween(8, 20));
                $grade->setType($faker->randomElement($gradeTypes));
                $grade->setCoefficient($faker->numberBetween(1, 3));
                $grade->setCreatedAt(new \DateTime($faker->dateTimeBetween('-6 months')->format('Y-m-d H:i:s')));
                $manager->persist($grade);
            }
        }

        $manager->flush();
    }
}
