<?php

namespace App\Policies;

use App\Models\Patient;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class PatientPolicy
{
    use HandlesAuthorization;

    /**
     * Perform pre-authorization checks.
     */
    public function before(User $user, string $ability): ?bool
    {
        // If user is admin, grant all permissions
        if ($user->is_admin) {
            return true;
        }

        return null; // fall through to other policy methods
    }

    /**
     * Determine whether the user can view any patients.
     */
    public function viewAny(User $user): bool
    {
        // Restrict access to authenticated users only
        return $user !== null;
    }

    /**
     * Determine whether the user can view the patient.
     */
    public function view(User $user, Patient $patient): bool
    {
        return $user->id === $patient->user_id;
    }

    /**
     * Determine whether the user can create patients.
     */
    public function create(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can update the patient.
     */
    public function update(User $user, Patient $patient): bool
    {
        return $user->id === $patient->user_id;
    }

    /**
     * Determine whether the user can delete the patient.
     */
    public function delete(User $user, Patient $patient): bool
    {
        return $user->id === $patient->user_id;
    }
}
