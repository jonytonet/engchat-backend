<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * 
 *
 * @OA\Schema (
 *     schema="QueueRule",
 *     type="object",
 *     title="Queue Rule",
 *     description="Regras de funcionamento das filas por departamento",
 *     @OA\Property(property="id", type="integer", description="Rule ID"),
 *     @OA\Property(property="department_id", type="integer", description="Department ID"),
 *     @OA\Property(property="max_queue_size", type="integer", description="Maximum queue size"),
 *     @OA\Property(property="max_wait_time_minutes", type="integer", description="Maximum wait time in minutes"),
 *     @OA\Property(property="first_notification_after_minutes", type="integer", description="First notification delay"),
 *     @OA\Property(property="notification_interval_minutes", type="integer", description="Notification interval"),
 *     @OA\Property(property="max_notifications", type="integer", description="Maximum notifications count"),
 *     @OA\Property(property="priority_rules", type="object", description="Priority rules configuration"),
 *     @OA\Property(property="vip_priority_enabled", type="boolean", description="VIP priority enabled"),
 *     @OA\Property(property="working_hours", type="object", description="Working hours configuration"),
 *     @OA\Property(property="auto_assignment_enabled", type="boolean", description="Auto assignment enabled"),
 *     @OA\Property(property="assignment_algorithm", type="string", enum={"round_robin", "least_busy", "skill_based"}),
 *     @OA\Property(property="escalation_enabled", type="boolean", description="Escalation enabled"),
 *     @OA\Property(property="escalation_time_minutes", type="integer", description="Escalation time in minutes"),
 *     @OA\Property(property="is_active", type="boolean", description="Is rule active")
 * )
 * @property int $id
 * @property int $department_id
 * @property int $max_queue_size
 * @property int $max_wait_time_minutes
 * @property int $first_notification_after_minutes
 * @property int $notification_interval_minutes
 * @property int $max_notifications
 * @property int|null $welcome_template_id
 * @property int|null $position_update_template_id
 * @property int|null $timeout_template_id
 * @property int|null $assigned_template_id
 * @property array<array-key, mixed>|null $priority_rules Regras para priorização automática
 * @property bool $vip_priority_enabled
 * @property array<array-key, mixed>|null $working_hours
 * @property int|null $out_of_hours_template_id
 * @property bool $auto_assignment_enabled
 * @property string $assignment_algorithm
 * @property bool $escalation_enabled
 * @property int $escalation_time_minutes
 * @property int|null $escalation_department_id
 * @property bool $is_active
 * @property int $created_by
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\MessageTemplate|null $assignedTemplate
 * @property-read \App\Models\User $createdBy
 * @property-read \App\Models\Department $department
 * @property-read \App\Models\Department|null $escalationDepartment
 * @property-read \App\Models\MessageTemplate|null $outOfHoursTemplate
 * @property-read \App\Models\MessageTemplate|null $positionUpdateTemplate
 * @property-read \App\Models\MessageTemplate|null $timeoutTemplate
 * @property-read \App\Models\MessageTemplate|null $welcomeTemplate
 * @method static \Illuminate\Database\Eloquent\Builder<static>|QueueRule active()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|QueueRule byDepartment(int $departmentId)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|QueueRule newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|QueueRule newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|QueueRule query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|QueueRule whereAssignedTemplateId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|QueueRule whereAssignmentAlgorithm($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|QueueRule whereAutoAssignmentEnabled($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|QueueRule whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|QueueRule whereCreatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|QueueRule whereDepartmentId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|QueueRule whereEscalationDepartmentId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|QueueRule whereEscalationEnabled($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|QueueRule whereEscalationTimeMinutes($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|QueueRule whereFirstNotificationAfterMinutes($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|QueueRule whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|QueueRule whereIsActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|QueueRule whereMaxNotifications($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|QueueRule whereMaxQueueSize($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|QueueRule whereMaxWaitTimeMinutes($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|QueueRule whereNotificationIntervalMinutes($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|QueueRule whereOutOfHoursTemplateId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|QueueRule wherePositionUpdateTemplateId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|QueueRule wherePriorityRules($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|QueueRule whereTimeoutTemplateId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|QueueRule whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|QueueRule whereVipPriorityEnabled($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|QueueRule whereWelcomeTemplateId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|QueueRule whereWorkingHours($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|QueueRule withAutoAssignment()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|QueueRule withEscalation()
 * @mixin \Eloquent
 */
class QueueRule extends Model
{
    use HasFactory;

    protected $fillable = [
        'department_id',
        'max_queue_size',
        'max_wait_time_minutes',
        'first_notification_after_minutes',
        'notification_interval_minutes',
        'max_notifications',
        'welcome_template_id',
        'position_update_template_id',
        'timeout_template_id',
        'assigned_template_id',
        'priority_rules',
        'vip_priority_enabled',
        'working_hours',
        'out_of_hours_template_id',
        'auto_assignment_enabled',
        'assignment_algorithm',
        'escalation_enabled',
        'escalation_time_minutes',
        'escalation_department_id',
        'is_active',
        'created_by',
    ];

    protected $casts = [
        'max_queue_size' => 'integer',
        'max_wait_time_minutes' => 'integer',
        'first_notification_after_minutes' => 'integer',
        'notification_interval_minutes' => 'integer',
        'max_notifications' => 'integer',
        'escalation_time_minutes' => 'integer',
        'priority_rules' => 'array',
        'working_hours' => 'array',
        'vip_priority_enabled' => 'boolean',
        'auto_assignment_enabled' => 'boolean',
        'escalation_enabled' => 'boolean',
        'is_active' => 'boolean',
    ];

    // ========================================
    // Relationships
    // ========================================

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function welcomeTemplate(): BelongsTo
    {
        return $this->belongsTo(MessageTemplate::class, 'welcome_template_id');
    }

    public function positionUpdateTemplate(): BelongsTo
    {
        return $this->belongsTo(MessageTemplate::class, 'position_update_template_id');
    }

    public function timeoutTemplate(): BelongsTo
    {
        return $this->belongsTo(MessageTemplate::class, 'timeout_template_id');
    }

    public function assignedTemplate(): BelongsTo
    {
        return $this->belongsTo(MessageTemplate::class, 'assigned_template_id');
    }

    public function outOfHoursTemplate(): BelongsTo
    {
        return $this->belongsTo(MessageTemplate::class, 'out_of_hours_template_id');
    }

    public function escalationDepartment(): BelongsTo
    {
        return $this->belongsTo(Department::class, 'escalation_department_id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // ========================================
    // Scopes
    // ========================================

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByDepartment($query, int $departmentId)
    {
        return $query->where('department_id', $departmentId);
    }

    public function scopeWithAutoAssignment($query)
    {
        return $query->where('auto_assignment_enabled', true);
    }

    public function scopeWithEscalation($query)
    {
        return $query->where('escalation_enabled', true);
    }

    // ========================================
    // Helper Methods
    // ========================================

    public function isQueueFull(int $currentSize): bool
    {
        return $currentSize >= $this->max_queue_size;
    }

    public function hasExceededMaxWaitTime(int $waitTimeMinutes): bool
    {
        return $waitTimeMinutes >= $this->max_wait_time_minutes;
    }

    public function shouldSendFirstNotification(int $waitTimeMinutes): bool
    {
        return $waitTimeMinutes >= $this->first_notification_after_minutes;
    }

    public function shouldSendPeriodicNotification(int $waitTimeMinutes, int $notificationCount): bool
    {
        if ($notificationCount >= $this->max_notifications) {
            return false;
        }

        $timeForNextNotification = $this->first_notification_after_minutes +
            ($notificationCount * $this->notification_interval_minutes);

        return $waitTimeMinutes >= $timeForNextNotification;
    }

    public function shouldEscalate(int $waitTimeMinutes): bool
    {
        return $this->escalation_enabled &&
            $waitTimeMinutes >= $this->escalation_time_minutes;
    }

    public function isWithinWorkingHours(): bool
    {
        if (empty($this->working_hours)) {
            return true; // 24/7 if no working hours defined
        }

        $now = now();
        $dayOfWeek = strtolower($now->format('l')); // monday, tuesday, etc.

        $todayHours = $this->working_hours[$dayOfWeek] ?? null;

        if (!$todayHours || !isset($todayHours['start'], $todayHours['end'])) {
            return false; // No working hours for today
        }

        $currentTime = $now->format('H:i');

        return $currentTime >= $todayHours['start'] &&
            $currentTime <= $todayHours['end'];
    }

    public function getAssignmentAlgorithmName(): string
    {
        return match ($this->assignment_algorithm) {
            'round_robin' => 'Round Robin',
            'least_busy' => 'Menos Ocupado',
            'skill_based' => 'Baseado em Habilidades',
            default => 'Automático'
        };
    }

    public function getPriorityMultiplier(string $priority): float
    {
        if (empty($this->priority_rules)) {
            return 1.0;
        }

        return match ($priority) {
            'urgent' => $this->priority_rules['urgent'] ?? 4.0,
            'high' => $this->priority_rules['high'] ?? 3.0,
            'medium' => $this->priority_rules['medium'] ?? 2.0,
            'low' => $this->priority_rules['low'] ?? 1.0,
            default => 1.0
        };
    }

    public function activate(): void
    {
        $this->update(['is_active' => true]);
    }

    public function deactivate(): void
    {
        $this->update(['is_active' => false]);
    }
}
