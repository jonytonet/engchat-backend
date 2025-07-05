<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * 
 *
 * @OA\Schema (
 *     schema="Category",
 *     title="Category",
 *     description="Model representing a conversation category for classification and routing",
 *     required={"name"},
 *     @OA\Property(property="id", type="integer", format="int64", description="Category ID", example=1),
 *     @OA\Property(property="name", type="string", description="Category name", example="Technical Support"),
 *     @OA\Property(property="description", type="string", nullable=true, description="Category description", example="Technical issues and troubleshooting"),
 *     @OA\Property(property="color", type="string", description="Category color for UI", example="#667eea"),
 *     @OA\Property(property="parent_id", type="integer", nullable=true, description="Parent category ID for hierarchy", example=null),
 *     @OA\Property(property="priority", type="integer", description="Priority level (1=low, 3=high)", example=2),
 *     @OA\Property(property="estimated_time", type="integer", nullable=true, description="Estimated resolution time in minutes", example=30),
 *     @OA\Property(property="auto_responses", type="object", nullable=true, description="Automated response configuration"),
 *     @OA\Property(property="requires_specialist", type="boolean", description="Whether category requires specialist", example=false),
 *     @OA\Property(property="is_active", type="boolean", description="Whether category is active", example=true),
 *     @OA\Property(property="created_at", type="string", format="date-time", description="Creation timestamp"),
 *     @OA\Property(property="updated_at", type="string", format="date-time", description="Last update timestamp")
 * )
 * @property int $id
 * @property string $name
 * @property string|null $description
 * @property string $color
 * @property int|null $parent_id
 * @property int $priority
 * @property int|null $estimated_time Tempo estimado em minutos
 * @property array<array-key, mixed>|null $auto_responses
 * @property bool $requires_specialist
 * @property bool $is_active
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\AutoResponse> $autoResponses
 * @property-read int|null $auto_responses_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Category> $children
 * @property-read int|null $children_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Conversation> $conversations
 * @property-read int|null $conversations_count
 * @property-read string $full_path
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\CategoryKeyword> $keywords
 * @property-read int|null $keywords_count
 * @property-read Category|null $parent
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\User> $specialists
 * @property-read int|null $specialists_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\User> $users
 * @property-read int|null $users_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Category active()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Category byPriority(int $priority)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Category byPriorityOrder()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Category newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Category newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Category onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Category query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Category requiresSpecialist()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Category root()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Category whereAutoResponses($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Category whereColor($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Category whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Category whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Category whereEstimatedTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Category whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Category whereIsActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Category whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Category whereParentId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Category wherePriority($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Category whereRequiresSpecialist($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Category whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Category withTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Category withoutTrashed()
 * @mixin \Eloquent
 */

class Category extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'description',
        'color',
        'parent_id',
        'priority',
        'estimated_time',
        'auto_responses',
        'requires_specialist',
        'is_active',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'auto_responses' => 'array',
        'requires_specialist' => 'boolean',
        'is_active' => 'boolean',
    ];

    // ===== RELATIONSHIPS =====

    /**
     * Get the parent category.
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'parent_id');
    }

    /**
     * Get child categories.
     */
    public function children(): HasMany
    {
        return $this->hasMany(Category::class, 'parent_id');
    }

    /**
     * Get conversations in this category.
     */
    public function conversations(): HasMany
    {
        return $this->hasMany(Conversation::class);
    }

    /**
     * Get users specialized in this category.
     */
    public function specialists(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'user_categories')
                    ->withPivot('priority_level', 'is_specialist')
                    ->wherePivot('is_specialist', true)
                    ->withTimestamps();
    }

    /**
     * Get all users associated with this category.
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'user_categories')
                    ->withPivot('priority_level', 'is_specialist')
                    ->withTimestamps();
    }

    /**
     * Get keywords associated with this category.
     */
    public function keywords(): HasMany
    {
        return $this->hasMany(CategoryKeyword::class);
    }

    /**
     * Get auto responses for this category.
     */
    public function autoResponses(): HasMany
    {
        return $this->hasMany(AutoResponse::class);
    }

    // ===== SCOPES =====

    /**
     * Scope to get only active categories.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to get root categories (no parent).
     */
    public function scopeRoot($query)
    {
        return $query->whereNull('parent_id');
    }

    /**
     * Scope to get categories by priority.
     */
    public function scopeByPriority($query, int $priority)
    {
        return $query->where('priority', $priority);
    }

    /**
     * Scope to get categories that require specialist.
     */
    public function scopeRequiresSpecialist($query)
    {
        return $query->where('requires_specialist', true);
    }

    /**
     * Scope to order by priority.
     */
    public function scopeByPriorityOrder($query)
    {
        return $query->orderBy('priority', 'desc');
    }

    // ===== METHODS =====

    /**
     * Check if category is active.
     */
    public function isActive(): bool
    {
        return $this->is_active;
    }

    /**
     * Check if category requires specialist.
     */
    public function requiresSpecialist(): bool
    {
        return $this->requires_specialist;
    }

    /**
     * Check if category is root (has no parent).
     */
    public function isRoot(): bool
    {
        return is_null($this->parent_id);
    }

    /**
     * Check if category has children.
     */
    public function hasChildren(): bool
    {
        return $this->children()->exists();
    }

    /**
     * Get available specialists for this category.
     */
    public function getAvailableSpecialists()
    {
        return $this->specialists()
            ->where('is_active', true)
            ->where('status', 'online')
            ->get()
            ->filter(function ($user) {
                return $user->canTakeMoreConversations();
            });
    }

    /**
     * Get the full category path (including parents).
     */
    public function getFullPathAttribute(): string
    {
        $path = [];
        $category = $this;

        while ($category) {
            array_unshift($path, $category->name);
            $category = $category->parent;
        }

        return implode(' > ', $path);
    }

    /**
     * Get category statistics.
     */
    public function getStats(): array
    {
        return [
            'total_conversations' => $this->conversations()->count(),
            'open_conversations' => $this->conversations()->where('status', 'open')->count(),
            'pending_conversations' => $this->conversations()->where('status', 'pending')->count(),
            'closed_conversations' => $this->conversations()->where('status', 'closed')->count(),
            'specialists_count' => $this->specialists()->count(),
            'available_specialists' => $this->getAvailableSpecialists()->count(),
            'avg_resolution_time' => $this->getAverageResolutionTime(),
        ];
    }

    /**
     * Get average resolution time for this category.
     */
    public function getAverageResolutionTime(): ?int
    {
        return $this->conversations()
            ->whereNotNull('resolution_time')
            ->avg('resolution_time');
    }

    /**
     * Auto-assign conversation to best available specialist.
     */
    public function autoAssignConversation(Conversation $conversation): ?User
    {
        $specialists = $this->getAvailableSpecialists();

        if ($specialists->isEmpty()) {
            return null;
        }

        // Get specialist with least active conversations
        return $specialists->sortBy(function ($specialist) {
            return $specialist->assignedConversations()
                ->whereIn('status', ['open', 'pending'])
                ->count();
        })->first();
    }
}
