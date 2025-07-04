<?php

namespace App\Services;

use App\Models\User;
use App\Repositories\Contracts\ConversationRepositoryInterface;

class UserPermissionService
{
    public function __construct(
        private ConversationRepositoryInterface $conversationRepository
    ) {}

    /**
     * Check if user can take more conversations based on role limits.
     */
    public function canTakeMoreConversations(User $user): bool
    {
        if (!$user->role) {
            return false;
        }

        $activeConversations = $this->getActiveConversationsCount($user);

        return $activeConversations < $user->role->max_simultaneous_chats;
    }

    /**
     * Check if user has a specific permission.
     */
    public function hasPermission(User $user, string $permission): bool
    {
        if (!$user->role || !$user->role->permissions) {
            return false;
        }

        $permissions = is_array($user->role->permissions)
            ? $user->role->permissions
            : json_decode($user->role->permissions, true);

        return in_array($permission, $permissions ?? []);
    }

    /**
     * Get the count of active conversations for a user.
     */
    public function getActiveConversationsCount(User $user): int
    {
        // Use the existing method from ConversationRepository
        return $user->assignedConversations()
            ->whereIn('status', ['open', 'pending', 'assigned'])
            ->count();
    }

    /**
     * Check if user is currently available for new assignments.
     */
    public function isAvailableForAssignment(User $user): bool
    {
        if (!$user->is_active) {
            return false;
        }

        if ($user->status !== 'online') {
            return false;
        }

        return $this->canTakeMoreConversations($user);
    }

    /**
     * Get user's permission list.
     */
    public function getUserPermissions(User $user): array
    {
        if (!$user->role || !$user->role->permissions) {
            return [];
        }

        return is_array($user->role->permissions)
            ? $user->role->permissions
            : json_decode($user->role->permissions, true) ?? [];
    }
}
