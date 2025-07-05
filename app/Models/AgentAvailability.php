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
 *     schema="AgentAvailability",
 *     type="object",
 *     title="Agent Availability",
 *     description="Controle de disponibilidade dos agentes em tempo real",
 *     @OA\Property(property="id", type="integer", description="Availability ID"),
 *     @OA\Property(property="agent_id", type="integer", description="Agent user ID"),
 *     @OA\Property(property="current_status", type="string", enum={"online", "offline", "busy", "away", "break"}),
 *     @OA\Property(property="previous_status", type="string", enum={"online", "offline", "busy", "away", "break"}),
 *     @OA\Property(property="max_conversations", type="integer", description="Maximum simultaneous conversations"),
 *     @OA\Property(property="current_conversations_count", type="integer", description="Current active conversations"),
 *     @OA\Property(property="available_slots", type="integer", description="Available conversation slots"),
 *     @OA\Property(property="available_departments", type="array", @OA\Items(type="integer")),
 *     @OA\Property(property="preferred_categories", type="array", @OA\Items(type="integer")),
 *     @OA\Property(property="break_reason", type="string", description="Reason for break status"),
 *     @OA\Property(property="auto_accept_conversations", type="boolean", description="Auto-accept new conversations"),
 *     @OA\Property(property="accept_transfers", type="boolean", description="Accept transferred conversations"),
 *     @OA\Property(property="last_status_change", type="string", format="date-time"),
 *     @OA\Property(property="last_activity", type="string", format="date-time")
 * )
 * @property int $id
 * @property int $agent_id
 * @property string $current_status
 * @property string|null $previous_status
 * @property int $max_conversations
 * @property int $current_conversations_count
 * @property int|null $available_slots
 * @property array<array-key, mixed>|null $available_departments IDs dos departamentos que pode atender
 * @property array<array-key, mixed>|null $preferred_categories Categorias de preferência
 * @property string|null $break_reason
 * @property \Illuminate\Support\Carbon|null $break_start_time
 * @property \Illuminate\Support\Carbon|null $estimated_return_time
 * @property bool $auto_accept_conversations
 * @property bool $accept_transfers
 * @property \Illuminate\Support\Carbon $last_status_change
 * @property \Illuminate\Support\Carbon $last_activity
 * @property \Illuminate\Support\Carbon|null $shift_start_time
 * @property \Illuminate\Support\Carbon|null $shift_end_time
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\User $agent
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AgentAvailability acceptingTransfers()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AgentAvailability autoAccepting()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AgentAvailability available()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AgentAvailability byCategory(int $categoryId)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AgentAvailability byDepartment(int $departmentId)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AgentAvailability newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AgentAvailability newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AgentAvailability online()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AgentAvailability query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AgentAvailability whereAcceptTransfers($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AgentAvailability whereAgentId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AgentAvailability whereAutoAcceptConversations($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AgentAvailability whereAvailableDepartments($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AgentAvailability whereAvailableSlots($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AgentAvailability whereBreakReason($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AgentAvailability whereBreakStartTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AgentAvailability whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AgentAvailability whereCurrentConversationsCount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AgentAvailability whereCurrentStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AgentAvailability whereEstimatedReturnTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AgentAvailability whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AgentAvailability whereLastActivity($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AgentAvailability whereLastStatusChange($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AgentAvailability whereMaxConversations($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AgentAvailability wherePreferredCategories($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AgentAvailability wherePreviousStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AgentAvailability whereShiftEndTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AgentAvailability whereShiftStartTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AgentAvailability whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class AgentAvailability extends Model
{
    use HasFactory;

    protected $table = 'agent_availability';

    protected $fillable = [
        'agent_id',
        'current_status',
        'previous_status',
        'max_conversations',
        'current_conversations_count',
        'available_departments',
        'preferred_categories',
        'break_reason',
        'break_start_time',
        'estimated_return_time',
        'auto_accept_conversations',
        'accept_transfers',
        'last_status_change',
        'last_activity',
        'shift_start_time',
        'shift_end_time',
    ];

    protected $casts = [
        'available_departments' => 'array',
        'preferred_categories' => 'array',
        'max_conversations' => 'integer',
        'current_conversations_count' => 'integer',
        'auto_accept_conversations' => 'boolean',
        'accept_transfers' => 'boolean',
        'break_start_time' => 'datetime',
        'estimated_return_time' => 'datetime',
        'last_status_change' => 'datetime',
        'last_activity' => 'datetime',
        'shift_start_time' => 'datetime',
        'shift_end_time' => 'datetime',
    ];

    // ========================================
    // Relationships
    // ========================================

    public function agent(): BelongsTo
    {
        return $this->belongsTo(User::class, 'agent_id');
    }

    // ========================================
    // Scopes
    // ========================================

    public function scopeOnline($query)
    {
        return $query->where('current_status', 'online');
    }

    public function scopeAvailable($query)
    {
        return $query->where('current_status', 'online')
            ->whereRaw('current_conversations_count < max_conversations');
    }

    public function scopeAutoAccepting($query)
    {
        return $query->where('auto_accept_conversations', true);
    }

    public function scopeAcceptingTransfers($query)
    {
        return $query->where('accept_transfers', true);
    }

    public function scopeByDepartment($query, int $departmentId)
    {
        return $query->whereJsonContains('available_departments', $departmentId);
    }

    public function scopeByCategory($query, int $categoryId)
    {
        return $query->whereJsonContains('preferred_categories', $categoryId);
    }

    // ========================================
    // Helper Methods
    // ========================================

    public function isAvailable(): bool
    {
        return $this->current_status === 'online' &&
            $this->current_conversations_count < $this->max_conversations;
    }

    public function canAcceptNewConversation(): bool
    {
        return $this->isAvailable() && $this->auto_accept_conversations;
    }

    public function canAcceptTransfer(): bool
    {
        return $this->isAvailable() && $this->accept_transfers;
    }

    public function getAvailableSlots(): int
    {
        return max(0, $this->max_conversations - $this->current_conversations_count);
    }

    public function updateStatus(string $status, string $reason = null): void
    {
        $this->update([
            'previous_status' => $this->current_status,
            'current_status' => $status,
            'last_status_change' => now(),
            'last_activity' => now(),
            'break_reason' => $status === 'break' ? $reason : null,
            'break_start_time' => $status === 'break' ? now() : null,
        ]);
    }

    public function incrementConversationCount(): void
    {
        $this->increment('current_conversations_count');
        $this->touch('last_activity');
    }

    public function decrementConversationCount(): void
    {
        $this->decrement('current_conversations_count');
        $this->touch('last_activity');
    }

    public function updateActivity(): void
    {
        $this->touch('last_activity');
    }

    public function startShift(): void
    {
        $this->update([
            'shift_start_time' => now(),
            'current_status' => 'online',
            'previous_status' => $this->current_status,
            'last_status_change' => now(),
            'last_activity' => now(),
        ]);
    }

    public function endShift(): void
    {
        $this->update([
            'shift_end_time' => now(),
            'current_status' => 'offline',
            'previous_status' => $this->current_status,
            'last_status_change' => now(),
        ]);
    }

    public function setDepartments(array $departmentIds): void
    {
        $this->update(['available_departments' => $departmentIds]);
    }

    public function setPreferredCategories(array $categoryIds): void
    {
        $this->update(['preferred_categories' => $categoryIds]);
    }
}
