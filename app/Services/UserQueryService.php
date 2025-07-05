<?php

declare(strict_types=1);

namespace App\Services;

use App\DTOs\UserDTO;
use App\Models\User;

/**
 * Service responsible for user queries and lookups.
 * 
 * Following Single Responsibility Principle:
 * - ONLY handles user query operations
 * - NO business logic beyond basic lookups
 * - NO data persistence operations
 */
final readonly class UserQueryService
{
    /**
     * Find user by ERP user ID.
     */
    public function findByErpUserId(string $erpUserId): ?UserDTO
    {
        $user = User::where('erp_user_id', $erpUserId)->first();

        return $user ? UserDTO::fromModel($user) : null;
    }

    /**
     * Get users with ERP integration.
     */
    public function getUsersWithErpIntegration(): array
    {
        $users = User::whereNotNull('erp_user_id')->get();

        return $users->map(fn($user) => UserDTO::fromModel($user))->toArray();
    }

    /**
     * Check if ERP user ID is already in use.
     */
    public function isErpUserIdInUse(string $erpUserId): bool
    {
        return User::where('erp_user_id', $erpUserId)->exists();
    }
}
