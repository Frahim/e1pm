<?php

namespace App\Policies;

use App\Models\Project;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class ProjectPolicy
{
    use HandlesAuthorization;

    /**
     * Any admin or manager (global) can create a project.
     */
    public function create(User $user): bool
    {
        return $user->isAdmin() || $user->isManager();
    }

    /**
     * View — owner, project member, or any admin can view.
     */
    public function view(User $user, Project $project): bool
    {
        if ($user->isAdmin()) return true;
        if ($project->owner_id === $user->id) return true;
        return $project->members()->where('user_id', $user->id)->exists();
    }

    /**
     * Update — project owner or project manager (pivot) OR global admin can update.
     */
    public function update(User $user, Project $project): bool
    {
        if ($user->isAdmin()) return true;
        if ($project->owner_id === $user->id) return true;

        $member = $project->members()->where('user_id', $user->id)->first();
        if ($member && $member->pivot->role === 'manager') return true;

        // global manager cannot edit unless they are a member of project or admin
        return false;
    }

    /**
     * Delete — only owner or global admin.
     */
    public function delete(User $user, Project $project): bool
    {
        if ($user->isAdmin()) return true;
        return $project->owner_id === $user->id;
    }

    /**
     * manageMembers — owner, project manager (pivot), or admin.
     */
    public function manageMembers(User $user, Project $project): bool
    {
        if ($user->isAdmin()) return true;
        if ($project->owner_id === $user->id) return true;

        $member = $project->members()->where('user_id', $user->id)->first();
        return $member && $member->pivot->role === 'manager';
    }

    /**
     * createTask — for now allow any project member or project owner/manager; admins also allowed.
     */
    public function createTask(User $user, Project $project): bool
    {
        if ($user->isAdmin()) return true;
        if ($project->owner_id === $user->id) return true;
        return $project->members()->where('user_id', $user->id)->exists();
    }
}
