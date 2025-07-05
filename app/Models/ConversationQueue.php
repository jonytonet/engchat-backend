<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * 
 *
 * @OA\Schema (
 *     schema="ConversationQueue",
 *     type="object",
 *     title="Conversation Queue",
 *     description="Sistema de filas de atendimento",
 *     @OA\Property(property="id", type="integer", description="Queue ID"),
 *     @OA\Property(property="conversation_id", type="integer", description="Conversation ID"),
 *     @OA\Property(property="department_id", type="integer", description="Department ID"),
 *     @OA\Property(property="category_id", type="integer", description="Category ID"),
 *     @OA\Property(property="priority", type="string", enum={"low", "medium", "high", "urgent"}),
 *     @OA\Property(property="queue_position", type="integer", description="Position in queue"),
 *     @OA\Property(property="estimated_wait_time", type="integer", description="Estimated wait time in minutes"),
 *     @OA\Property(property="assignment_attempts", type="integer", description="Assignment attempts count"),
 *     @OA\Property(property="notification_count", type="integer", description="Notification count"),
 *     @OA\Property(property="status", type="string", enum={"waiting", "assigned", "expired", "cancelled"}),
 *     @OA\Property(property="assigned_to", type="integer", description="Assigned agent ID"),
 *     @OA\Property(property="entered_queue_at", type="string", format="date-time"),
 *     @OA\Property(property="assigned_at", type="string", format="date-time"),
 *     @OA\Property(property="removed_from_queue_at", type="string", format="date-time")
 * )
 * @property int $id
 * @property int $conversation_id
 * @property int $department_id
 * @property int|null $category_id
 * @property string $priority
 * @property int $queue_position
 * @property int|null $estimated_wait_time Tempo estimado em minutos
 * @property int $assignment_attempts
 * @property \Illuminate\Support\Carbon|null $last_assignment_attempt
 * @property \Illuminate\Support\Carbon|null $last_position_notification
 * @property int $notification_count
 * @property string $status
 * @property int|null $assigned_to
 * @property \Illuminate\Support\Carbon|null $assigned_at
 * @property \Illuminate\Support\Carbon $entered_queue_at
 * @property \Illuminate\Support\Carbon|null $removed_from_queue_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\User|null $assignedAgent
 * @property-read \App\Models\Category|null $category
 * @property-read \App\Models\Conversation $conversation
 * @property-read \App\Models\Department $department
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\QueueNotification> $notifications
 * @property-read int|null $notifications_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ConversationQueue byDepartment(int $departmentId)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ConversationQueue byPriority(string $priority)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ConversationQueue newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ConversationQueue newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ConversationQueue orderedByPriority()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ConversationQueue query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ConversationQueue waiting()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ConversationQueue whereAssignedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ConversationQueue whereAssignedTo($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ConversationQueue whereAssignmentAttempts($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ConversationQueue whereCategoryId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ConversationQueue whereConversationId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ConversationQueue whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ConversationQueue whereDepartmentId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ConversationQueue whereEnteredQueueAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ConversationQueue whereEstimatedWaitTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ConversationQueue whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ConversationQueue whereLastAssignmentAttempt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ConversationQueue whereLastPositionNotification($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ConversationQueue whereNotificationCount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ConversationQueue wherePriority($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ConversationQueue whereQueuePosition($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ConversationQueue whereRemovedFromQueueAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ConversationQueue whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ConversationQueue whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class ConversationQueue extends Model
{
    use HasFactory;

    protected $table = 'conversation_queue';

    protected $fillable = [
        'conversation_id',
        'department_id',
        'category_id',
        'priority',
        'queue_position',
        'estimated_wait_time',
        'assignment_attempts',
        'last_assignment_attempt',
        'last_position_notification',
        'notification_count',
        'status',
        'assigned_to',
        'assigned_at',
        'entered_queue_at',
        'removed_from_queue_at',
    ];

    protected $casts = [
        'assignment_attempts' => 'integer',
        'notification_count' => 'integer',
        'queue_position' => 'integer',
        'estimated_wait_time' => 'integer',
        'last_assignment_attempt' => 'datetime',
        'last_position_notification' => 'datetime',
        'assigned_at' => 'datetime',
        'entered_queue_at' => 'datetime',
        'removed_from_queue_at' => 'datetime',
    ];

    // ========================================
    // Relationships
    // ========================================

    public function conversation(): BelongsTo
    {
        return $this->belongsTo(Conversation::class);
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function assignedAgent(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function notifications(): HasMany
    {
        return $this->hasMany(QueueNotification::class, 'queue_id');
    }

    // ========================================
    // Scopes
    // ========================================

    public function scopeWaiting($query)
    {
        return $query->where('status', 'waiting');
    }

    public function scopeByDepartment($query, int $departmentId)
    {
        return $query->where('department_id', $departmentId);
    }

    public function scopeByPriority($query, string $priority)
    {
        return $query->where('priority', $priority);
    }

    public function scopeOrderedByPriority($query)
    {
        return $query->orderByRaw("
            CASE priority 
                WHEN 'urgent' THEN 1
                WHEN 'high' THEN 2
                WHEN 'medium' THEN 3
                WHEN 'low' THEN 4
            END
        ")->orderBy('entered_queue_at');
    }

    // ========================================
    // Helper Methods
    // ========================================

    public function assignToAgent(int $agentId): void
    {
        $this->update([
            'assigned_to' => $agentId,
            'assigned_at' => now(),
            'status' => 'assigned',
            'removed_from_queue_at' => now(),
        ]);
    }

    public function incrementAssignmentAttempts(): void
    {
        $this->increment('assignment_attempts');
        $this->update(['last_assignment_attempt' => now()]);
    }

    public function updatePosition(int $newPosition): void
    {
        $this->update(['queue_position' => $newPosition]);
    }

    public function removeFromQueue(string $reason = 'completed'): void
    {
        $status = match ($reason) {
            'expired' => 'expired',
            'cancelled' => 'cancelled',
            default => 'assigned'
        };

        $this->update([
            'status' => $status,
            'removed_from_queue_at' => now(),
        ]);
    }

    public function shouldSendPositionNotification(): bool
    {
        if (is_null($this->last_position_notification)) {
            return true;
        }

        // Get queue rules for this department
        $queueRules = QueueRule::where('department_id', $this->department_id)->first();

        if (!$queueRules) {
            return false;
        }

        $intervalMinutes = $queueRules->notification_interval_minutes;
        $maxNotifications = $queueRules->max_notifications;

        return $this->notification_count < $maxNotifications
            && $this->last_position_notification->diffInMinutes(now()) >= $intervalMinutes;
    }
}
