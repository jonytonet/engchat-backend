<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * @OA\Schema(
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
