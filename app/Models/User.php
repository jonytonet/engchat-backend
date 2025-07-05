<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

/**
 * 
 *
 * @OA\Schema (
 *     schema="User",
 *     title="User",
 *     description="Model representing a system user (agent, manager, admin)",
 *     required={"name", "email", "password"},
 *     @OA\Property(property="id", type="integer", format="int64", description="User ID", example=1),
 *     @OA\Property(property="name", type="string", description="User full name", example="João Silva"),
 *     @OA\Property(property="email", type="string", format="email", description="User email address", example="joao@empresa.com"),
 *     @OA\Property(property="avatar", type="string", nullable=true, description="Profile photo URL", example="/storage/avatars/user123.jpg"),
 *     @OA\Property(property="status", type="string", enum={"online", "offline", "busy", "away"}, description="Current user status", example="online"),
 *     @OA\Property(property="role_id", type="integer", nullable=true, description="User role ID", example=2),
 *     @OA\Property(property="department_id", type="integer", nullable=true, description="Department ID", example=1),
 *     @OA\Property(property="manager_id", type="integer", nullable=true, description="Manager user ID", example=5),
 *     @OA\Property(property="last_activity", type="string", format="date-time", nullable=true, description="Last activity timestamp"),
 *     @OA\Property(property="timezone", type="string", description="User timezone", example="America/Sao_Paulo"),
 *     @OA\Property(property="language", type="string", description="User language preference", example="pt-BR"),
 *     @OA\Property(property="is_active", type="boolean", description="Whether user account is active", example=true),
 *     @OA\Property(property="email_verified_at", type="string", format="date-time", nullable=true, description="Email verification timestamp"),
 *     @OA\Property(property="created_at", type="string", format="date-time", description="Creation timestamp"),
 *     @OA\Property(property="updated_at", type="string", format="date-time", description="Last update timestamp")
 * )
 * @property int $id
 * @property string $name
 * @property string $email
 * @property string|null $avatar
 * @property string $status
 * @property int|null $role_id
 * @property int|null $department_id
 * @property int|null $manager_id
 * @property \Illuminate\Support\Carbon|null $last_activity
 * @property string $timezone
 * @property string $language
 * @property bool $is_active
 * @property \Illuminate\Support\Carbon|null $email_verified_at
 * @property string $password
 * @property string|null $remember_token
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Conversation> $assignedConversations
 * @property-read int|null $assigned_conversations_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Category> $categories
 * @property-read int|null $categories_count
 * @property-read \App\Models\Department|null $department
 * @property-read string|null $avatar_url
 * @property-read bool $is_online
 * @property-read \Illuminate\Database\Eloquent\Collection<int, User> $managedUsers
 * @property-read int|null $managed_users_count
 * @property-read User|null $manager
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Message> $messages
 * @property-read int|null $messages_count
 * @property-read \Illuminate\Notifications\DatabaseNotificationCollection<int, \Illuminate\Notifications\DatabaseNotification> $notifications
 * @property-read int|null $notifications_count
 * @property-read \App\Models\Role|null $role
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Laravel\Sanctum\PersonalAccessToken> $tokens
 * @property-read int|null $tokens_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\ConversationTransfer> $transfersMade
 * @property-read int|null $transfers_made_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\ConversationTransfer> $transfersReceived
 * @property-read int|null $transfers_received_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User active()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User byRole(string $roleName)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User byStatus(string $status)
 * @method static \Database\Factories\UserFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User online()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereAvatar($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereDepartmentId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereEmailVerifiedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereIsActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereLanguage($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereLastActivity($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereManagerId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User wherePassword($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereRememberToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereRoleId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereTimezone($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereUpdatedAt($value)
 * @mixin \Eloquent
 */

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasApiTokens;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'avatar',
        'status',
        'role_id',
        'department_id',
        'manager_id',
        'last_activity',
        'timezone',
        'language',
        'is_active',
        'erp_user_id',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'last_activity' => 'datetime',
            'is_active' => 'boolean',
        ];
    }

    // ===== RELATIONSHIPS =====

    /**
     * Get the role that the user belongs to.
     */
    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }

    /**
     * Get the department that the user belongs to.
     */
    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    /**
     * Get the manager of this user.
     */
    public function manager(): BelongsTo
    {
        return $this->belongsTo(User::class, 'manager_id');
    }

    /**
     * Get the users that this user manages.
     */
    public function managedUsers(): HasMany
    {
        return $this->hasMany(User::class, 'manager_id');
    }

    /**
     * Get the categories that this user specializes in.
     */
    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class, 'user_categories')
            ->withPivot('priority_level', 'is_specialist')
            ->withTimestamps();
    }

    /**
     * Get conversations assigned to this user.
     */
    public function assignedConversations(): HasMany
    {
        return $this->hasMany(Conversation::class, 'assigned_to');
    }

    /**
     * Get messages sent by this user.
     */
    public function messages(): HasMany
    {
        return $this->hasMany(Message::class, 'sender_id');
    }

    /**
     * Get conversation transfers made by this user.
     */
    public function transfersMade(): HasMany
    {
        return $this->hasMany(ConversationTransfer::class, 'from_user_id');
    }

    /**
     * Get conversation transfers received by this user.
     */
    public function transfersReceived(): HasMany
    {
        return $this->hasMany(ConversationTransfer::class, 'to_user_id');
    }



    // ===== SCOPES =====

    /**
     * Scope to get only active users.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to get users by status.
     */
    public function scopeByStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope to get online users.
     */
    public function scopeOnline($query)
    {
        return $query->where('status', 'online');
    }

    /**
     * Scope to get users by role.
     */
    public function scopeByRole($query, string $roleName)
    {
        return $query->whereHas('role', function ($q) use ($roleName) {
            $q->where('name', $roleName);
        });
    }

    /**
     * Scope to get users by ERP ID.
     */
    public function scopeByErpUserId($query, string $erpUserId)
    {
        return $query->where('erp_user_id', $erpUserId);
    }

    // ===== ACCESSORS & MUTATORS =====

    /**
     * Get the user's full avatar URL.
     */
    public function getAvatarUrlAttribute(): ?string
    {
        return $this->avatar ? url($this->avatar) : null;
    }

    /**
     * Check if user is online.
     */
    public function getIsOnlineAttribute(): bool
    {
        return $this->status === 'online';
    }

    // ===== ERP INTEGRATION ATTRIBUTES =====

    /**
     * Check if user has ERP integration.
     */
    public function hasErpIntegration(): bool
    {
        return !empty($this->erp_user_id);
    }

    /**
     * Get the ERP user identifier.
     */
    public function getErpUserId(): ?string
    {
        return $this->erp_user_id;
    }
}
