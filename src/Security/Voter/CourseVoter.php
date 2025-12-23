<?php

namespace App\Security\Voter;

use App\Entity\Course;
use App\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class CourseVoter extends Voter
{
    public const VIEW = 'VIEW';
    public const EDIT = 'EDIT';
    public const DELETE = 'DELETE';

    protected function supports(string $attribute, mixed $subject): bool
    {
        if (!in_array($attribute, [self::VIEW, self::EDIT, self::DELETE])) {
            return false;
        }

        if (!$subject instanceof Course) {
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

        /** @var Course $course */
        $course = $subject;

        return match ($attribute) {
            self::VIEW => $this->canView($course, $user),
            self::EDIT => $this->canEdit($course, $user),
            self::DELETE => $this->canDelete($course, $user),
            default => false,
        };
    }

    private function canView(Course $course, User $user): bool
    {
        // Teachers can view their courses, admins can view all
        return in_array('ROLE_ADMIN', $user->getRoles())
            || $course->getTeacher() === $user;
    }

    private function canEdit(Course $course, User $user): bool
    {
        // Only the teacher who owns the course can edit, or admins
        return in_array('ROLE_ADMIN', $user->getRoles())
            || $course->getTeacher() === $user;
    }

    private function canDelete(Course $course, User $user): bool
    {
        // Only the teacher who owns the course can delete, or admins
        return in_array('ROLE_ADMIN', $user->getRoles())
            || $course->getTeacher() === $user;
    }
}
