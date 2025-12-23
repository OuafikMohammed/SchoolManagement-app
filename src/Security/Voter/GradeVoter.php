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

    protected function supports(string $attribute, mixed $subject): bool
    {
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

        /** @var Grade $grade */
        $grade = $subject;

        return match ($attribute) {
            self::VIEW => $this->canView($grade, $user),
            self::EDIT => $this->canEdit($grade, $user),
            self::DELETE => $this->canDelete($grade, $user),
            default => false,
        };
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
