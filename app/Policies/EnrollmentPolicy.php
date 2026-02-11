<?php

namespace App\Policies;

use App\Models\User;
use App\Models\CourseEnrollment;

class EnrollmentPolicy
{
    /**
     * Determine if user can view any enrollments
     */
    public function viewAny(User $user): bool
    {
        return $user->isSuperAdmin() || $user->isAdmin();
    }

    /**
     * Determine if user can view the enrollment
     */
    public function view(User $user, CourseEnrollment $enrollment): bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }

        if ($user->isAdmin()) {
            return $this->belongsToSameSchool($user, $enrollment);
        }

        // Student can view their own enrollment
        if ($user->isStudent()) {
            return $user->id === $enrollment->user_id;
        }

        return false;
    }

    /**
     * Determine if user can create enrollments
     */
    public function create(User $user): bool
    {
        return $user->isSuperAdmin() || $user->isAdmin();
    }

    /**
     * Determine if user can update the enrollment
     */
    public function update(User $user, CourseEnrollment $enrollment): bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }

        if ($user->isAdmin()) {
            return $this->belongsToSameSchool($user, $enrollment);
        }

        return false;
    }

    /**
     * Determine if user can delete the enrollment
     */
    public function delete(User $user, CourseEnrollment $enrollment): bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }

        if ($user->isAdmin()) {
            return $this->belongsToSameSchool($user, $enrollment);
        }

        return false;
    }

    /**
     * Check if user and enrollment belong to same school
     */
    private function belongsToSameSchool(User $user, CourseEnrollment $enrollment): bool
    {
        if (!$user->school_id) {
            return false;
        }

        // Enrollment belongs to school via course relationship
        return $enrollment->course && $enrollment->course->school_id === $user->school_id;
    }
}
