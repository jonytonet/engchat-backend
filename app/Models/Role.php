<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @OA\Schema(
 *     schema="Role",
 *     title="Role",
 *     description="Model representing a user role with permissions and chat limits",
 *     required={"name"},
 *     @OA\Property(property="id", type="integer", format="int64", description="Role ID", example=1),
 *     @OA\Property(property="name", type="string", description="Role name", example="agent"),
 *     @OA\Property(property="description", type="string", nullable=true, description="Role description", example="Customer service agent"),
 *     @OA\Property(property="permissions", type="array", @OA\Items(type="string"), nullable=true, description="Array of permissions", example={"chat.view", "chat.respond"}),
 *     @OA\Property(property="can_transfer", type="boolean", description="Can transfer conversations", example=true),
 *     @OA\Property(property="can_close_tickets", type="boolean", description="Can close tickets", example=true),
 *     @OA\Property(property="max_simultaneous_chats", type="integer", description="Maximum simultaneous chats", example=5),
 *     @OA\Property(property="created_at", type="string", format="date-time", description="Creation timestamp"),
 *     @OA\Property(property="updated_at", type="string", format="date-time", description="Last update timestamp")
 * )
 */
class Role extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'description',
        'permissions',
        'can_transfer',
        'can_close_tickets',
        'max_simultaneous_chats',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'permissions' => 'array',
        'can_transfer' => 'boolean',
        'can_close_tickets' => 'boolean',
    ];

    // ===== RELATIONSHIPS =====

    /**
     * Get the users that belong to this role.
     */
    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    // ===== SCOPES =====

    /**
     * Scope to get roles that can transfer conversations.
     */
    public function scopeCanTransfer($query)
    {
        return $query->where('can_transfer', true);
    }

    /**
     * Scope to get roles that can close tickets.
     */
    public function scopeCanCloseTickets($query)
    {
        return $query->where('can_close_tickets', true);
    }

    // ===== METHODS =====

    /**
     * Check if role has specific permission.
     */
    public function hasPermission(string $permission): bool
    {
        if (!$this->permissions) {
            return false;
        }

        return in_array($permission, $this->permissions);
    }

    /**
     * Add permission to role.
     */
    public function addPermission(string $permission): void
    {
        $permissions = $this->permissions ?? [];

        if (!in_array($permission, $permissions)) {
            $permissions[] = $permission;
            $this->permissions = $permissions;
            $this->save();
        }
    }

    /**
     * Remove permission from role.
     */
    public function removePermission(string $permission): void
    {
        if (!$this->permissions) {
            return;
        }

        $permissions = array_filter($this->permissions, function ($p) use ($permission) {
            return $p !== $permission;
        });

        $this->permissions = array_values($permissions);
        $this->save();
    }

    /**
     * Check if role is admin.
     */
    public function isAdmin(): bool
    {
        return $this->name === 'admin';
    }

    /**
     * Check if role is manager.
     */
    public function isManager(): bool
    {
        return $this->name === 'manager';
    }

    /**
     * Check if role is agent.
     */
    public function isAgent(): bool
    {
        return $this->name === 'agent';
    }
}
