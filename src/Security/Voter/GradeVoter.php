<?php

namespace App\Security\Voter;

use App\Entity\Grade;
use App\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class GradeVoter extends Voter
{
    public const VIEW = 'VIEW';
    public const EDIT = 'EDIT';
    public const DELETE = 'DELETE';
    public const ADD = 'ADD';

    protected function supports(string $attribute, mixed $subject): bool
    {
        // ADD permission works on Course objects, others work on Grade objects
        if (self::ADD === $attribute) {
            // Subject should be a Course for ADD operations
            return true;
        }

        if (!in_array($attribute, [self::VIEW, self::EDIT, self::DELETE])) {
            return false;
        }

        if (!$subject instanceof Grade) {
            return false;
        }

        return true;
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();

        if (!$user instanceof User) {
            return false;
        }

        // Handle ADD permission on Course
        if (self::ADD === $attribute) {
            return $this->canAdd($subject, $user);
        }

        /** @var Grade $grade */
        $grade = $subject;

        return match ($attribute) {
            self::VIEW => $this->canView($grade, $user),
            self::EDIT => $this->canEdit($grade, $user),
            self::DELETE => $this->canDelete($grade, $user),
            default => false,
        };
    }

    private function canAdd(mixed $course, User $user): bool
    {
        // Teacher can add grades to their courses, admins can add to any course
        return in_array('ROLE_ADMIN', $user->getRoles())
            || (is_object($course) && method_exists($course, 'getTeacher') && $course->getTeacher() === $user);
    }

    private function canView(Grade $grade, User $user): bool
    {
        // Teacher can view grades in their course, student can view their own grades, admins can view all
        return in_array('ROLE_ADMIN', $user->getRoles())
            || $grade->getCourse()?->getTeacher() === $user
            || $grade->getStudent() === $user;
    }

    private function canEdit(Grade $grade, User $user): bool
    {
        // Only the teacher who owns the course can edit grades, or admins
        return in_array('ROLE_ADMIN', $user->getRoles())
            || $grade->getCourse()?->getTeacher() === $user;
    }

    private function canDelete(Grade $grade, User $user): bool
    {
        // Only the teacher who owns the course can delete grades, or admins
        return in_array('ROLE_ADMIN', $user->getRoles())
            || $grade->getCourse()?->getTeacher() === $user;
    }
}
