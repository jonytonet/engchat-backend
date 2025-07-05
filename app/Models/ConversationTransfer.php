<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * 
 *
 * @OA\Schema (
 *     schema="ConversationTransfer",
 *     type="object",
 *     title="Conversation Transfer",
 *     description="Transfer record between agents",
 *     @OA\Property(property="id", type="integer", description="Transfer ID"),
 *     @OA\Property(property="conversation_id", type="integer", description="Conversation ID"),
 *     @OA\Property(property="from_user_id", type="integer", description="User who transferred"),
 *     @OA\Property(property="to_user_id", type="integer", description="User who received"),
 *     @OA\Property(property="reason", type="string", enum={"workload", "expertise", "unavailable", "other"}),
 *     @OA\Property(property="notes", type="string", description="Transfer notes"),
 *     @OA\Property(property="status", type="string", enum={"pending", "accepted", "rejected"}),
 *     @OA\Property(property="transferred_at", type="string", format="date-time"),
 *     @OA\Property(property="accepted_at", type="string", format="date-time"),
 *     @OA\Property(property="created_at", type="string", format="date-time"),
 *     @OA\Property(property="updated_at", type="string", format="date-time")
 * )
 * @property int $id
 * @property int $conversation_id
 * @property int|null $from_user_id
 * @property int $to_user_id
 * @property string $reason
 * @property string|null $notes
 * @property string $status
 * @property \Illuminate\Support\Carbon $transferred_at
 * @property \Illuminate\Support\Carbon|null $accepted_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Conversation $conversation
 * @property-read \App\Models\User|null $fromUser
 * @property-read bool $is_accepted
 * @property-read bool $is_pending
 * @property-read bool $is_rejected
 * @property-read string $reason_label
 * @property-read string $status_label
 * @property-read \App\Models\User $toUser
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ConversationTransfer accepted()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ConversationTransfer byUser(int $userId)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ConversationTransfer newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ConversationTransfer newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ConversationTransfer pending()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ConversationTransfer query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ConversationTransfer recent(int $days = 30)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ConversationTransfer rejected()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ConversationTransfer whereAcceptedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ConversationTransfer whereConversationId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ConversationTransfer whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ConversationTransfer whereFromUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ConversationTransfer whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ConversationTransfer whereNotes($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ConversationTransfer whereReason($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ConversationTransfer whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ConversationTransfer whereToUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ConversationTransfer whereTransferredAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ConversationTransfer whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class ConversationTransfer extends Model
{
    use HasFactory;

    protected $fillable = [
        'conversation_id',
        'from_user_id',
        'to_user_id',
        'reason',
        'notes',
        'status',
        'transferred_at',
        'accepted_at',
    ];

    protected $casts = [
        'transferred_at' => 'datetime',
        'accepted_at' => 'datetime',
    ];

    // ========================================
    // Relationships
    // ========================================

    public function conversation(): BelongsTo
    {
        return $this->belongsTo(Conversation::class);
    }

    public function fromUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'from_user_id');
    }

    public function toUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'to_user_id');
    }

    // ========================================
    // Accessors & Mutators
    // ========================================

    public function getReasonLabelAttribute(): string
    {
        return match($this->reason) {
            'workload' => 'Sobrecarga de trabalho',
            'expertise' => 'Especialização necessária',
            'unavailable' => 'Indisponível',
            'other' => 'Outro motivo',
        };
    }

    public function getStatusLabelAttribute(): string
    {
        return match($this->status) {
            'pending' => 'Pendente',
            'accepted' => 'Aceita',
            'rejected' => 'Rejeitada',
        };
    }

    public function getIsPendingAttribute(): bool
    {
        return $this->status === 'pending';
    }

    public function getIsAcceptedAttribute(): bool
    {
        return $this->status === 'accepted';
    }

    public function getIsRejectedAttribute(): bool
    {
        return $this->status === 'rejected';
    }

    // ========================================
    // Scopes
    // ========================================

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeAccepted($query)
    {
        return $query->where('status', 'accepted');
    }

    public function scopeRejected($query)
    {
        return $query->where('status', 'rejected');
    }

    public function scopeByUser($query, int $userId)
    {
        return $query->where('to_user_id', $userId)
                     ->orWhere('from_user_id', $userId);
    }

    public function scopeRecent($query, int $days = 30)
    {
        return $query->where('transferred_at', '>=', now()->subDays($days));
    }
}
