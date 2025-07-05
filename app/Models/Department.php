<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * 
 *
 * @OA\Schema (
 *     schema="Department",
 *     title="Department",
 *     description="Model representing a department within the organization",
 *     required={"name"},
 *     @OA\Property(property="id", type="integer", format="int64", description="Department ID", example=1),
 *     @OA\Property(property="name", type="string", description="Department name", example="Customer Support"),
 *     @OA\Property(property="description", type="string", nullable=true, description="Department description", example="Handles customer inquiries and support tickets"),
 *     @OA\Property(property="manager_id", type="integer", nullable=true, description="Manager user ID", example=5),
 *     @OA\Property(property="is_active", type="boolean", description="Whether department is active", example=true),
 *     @OA\Property(property="working_hours", type="object", nullable=true, description="Working hours configuration", example={"monday": {"start": "08:00", "end": "18:00"}}),
 *     @OA\Property(property="auto_assignment_enabled", type="boolean", description="Whether auto-assignment is enabled", example=true),
 *     @OA\Property(property="created_at", type="string", format="date-time", description="Creation timestamp"),
 *     @OA\Property(property="updated_at", type="string", format="date-time", description="Last update timestamp")
 * )
 * @property int $id
 * @property string $name
 * @property string|null $description
 * @property int|null $manager_id
 * @property bool $is_active
 * @property array<array-key, mixed>|null $working_hours
 * @property bool $auto_assignment_enabled
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\User> $activeUsers
 * @property-read int|null $active_users_count
 * @property-read \App\Models\User|null $manager
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\User> $onlineUsers
 * @property-read int|null $online_users_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\User> $users
 * @property-read int|null $users_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Department active()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Department newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Department newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Department query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Department whereAutoAssignmentEnabled($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Department whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Department whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Department whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Department whereIsActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Department whereManagerId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Department whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Department whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Department whereWorkingHours($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Department withAutoAssignment()
 * @mixin \Eloquent
 */
class Department extends Model
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
        'manager_id',
        'is_active',
        'working_hours',
        'auto_assignment_enabled',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'is_active' => 'boolean',
        'working_hours' => 'array',
        'auto_assignment_enabled' => 'boolean',
    ];

    // ===== RELATIONSHIPS =====

    /**
     * Get the manager of this department.
     */
    public function manager(): BelongsTo
    {
        return $this->belongsTo(User::class, 'manager_id');
    }

    /**
     * Get all users in this department.
     */
    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    /**
     * Get active users in this department.
     */
    public function activeUsers(): HasMany
    {
        return $this->hasMany(User::class)->where('is_active', true);
    }

    /**
     * Get online users in this department.
     */
    public function onlineUsers(): HasMany
    {
        return $this->hasMany(User::class)->where('status', 'online');
    }

    // ===== SCOPES =====

    /**
     * Scope to get only active departments.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to get departments with auto-assignment enabled.
     */
    public function scopeWithAutoAssignment($query)
    {
        return $query->where('auto_assignment_enabled', true);
    }

    // ===== METHODS =====

    /**
     * Check if department is currently within working hours.
     */
    public function isWithinWorkingHours(): bool
    {
        if (!$this->working_hours) {
            return true; // No restrictions means always available
        }

        $now = now();
        $dayOfWeek = strtolower($now->format('l')); // monday, tuesday, etc.

        if (!isset($this->working_hours[$dayOfWeek])) {
            return false; // No working hours defined for this day
        }

        $hours = $this->working_hours[$dayOfWeek];
        $startTime = $now->copy()->setTimeFromTimeString($hours['start']);
        $endTime = $now->copy()->setTimeFromTimeString($hours['end']);

        return $now->between($startTime, $endTime);
    }

    /**
     * Get available agents for assignment.
     */
    public function getAvailableAgents()
    {
        return $this->users()
            ->active()
            ->online()
            ->whereHas('role', function ($query) {
                $query->where('name', 'agent');
            })
            ->get()
            ->filter(function ($user) {
                return $user->canTakeMoreConversations();
            });
    }

    /**
     * Get next available agent for assignment using round-robin.
     */
    public function getNextAgent(): ?User
    {
        $availableAgents = $this->getAvailableAgents();

        if ($availableAgents->isEmpty()) {
            return null;
        }

        // Simple round-robin: get agent with least active conversations
        return $availableAgents->sortBy(function ($agent) {
            return $agent->assignedConversations()
                ->whereIn('status', ['open', 'pending'])
                ->count();
        })->first();
    }

    /**
     * Get department statistics.
     */
    public function getStats(): array
    {
        return [
            'total_users' => $this->users()->count(),
            'active_users' => $this->activeUsers()->count(),
            'online_users' => $this->onlineUsers()->count(),
            'available_agents' => $this->getAvailableAgents()->count(),
            'is_within_working_hours' => $this->isWithinWorkingHours(),
            'auto_assignment_enabled' => $this->auto_assignment_enabled,
        ];
    }
}
