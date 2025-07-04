<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\ConversationStatus;
use App\Enums\Priority;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @OA\Schema(
 *     schema="Conversation",
 *     title="Conversation",
 *     description="Model representing a customer conversation",
 *     required={"id", "contact_id", "channel_id", "status", "subject"},
 *     @OA\Property(property="id", type="integer", format="int64", description="Conversation ID", example=1),
 *     @OA\Property(property="contact_id", type="integer", description="Contact ID", example=1),
 *     @OA\Property(property="channel_id", type="integer", description="Channel ID", example=1),
 *     @OA\Property(property="category_id", type="integer", nullable=true, description="Category ID", example=1),
 *     @OA\Property(property="assigned_to", type="integer", nullable=true, description="Assigned user ID", example=5),
 *     @OA\Property(property="status", type="string", enum={"open", "pending", "closed"}, description="Conversation status", example="open"),
 *     @OA\Property(property="priority", type="string", enum={"low", "medium", "high", "urgent"}, description="Priority level", example="medium"),
 *     @OA\Property(property="subject", type="string", description="Conversation subject", example="Problema com produto"),
 *     @OA\Property(property="last_message_at", type="string", format="date-time", nullable=true, description="Last message timestamp"),
 *     @OA\Property(property="closed_at", type="string", format="date-time", nullable=true, description="Closure timestamp"),
 *     @OA\Property(property="closed_by", type="integer", nullable=true, description="User who closed the conversation"),
 *     @OA\Property(property="metadata", type="object", nullable=true, description="Additional metadata"),
 *     @OA\Property(property="created_at", type="string", format="date-time", description="Creation timestamp"),
 *     @OA\Property(property="updated_at", type="string", format="date-time", description="Last update timestamp"),
 *     @OA\Property(property="deleted_at", type="string", format="date-time", nullable=true, description="Deletion timestamp"),
 *     @OA\Property(
 *         property="contact",
 *         ref="#/components/schemas/Contact",
 *         description="Associated contact"
 *     ),
 *     @OA\Property(
 *         property="channel",
 *         ref="#/components/schemas/Channel",
 *         description="Associated channel"
 *     )
 * )
 */
class Conversation extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'contact_id',
        'channel_id',
        'category_id',
        'assigned_to',
        'status',
        'priority',
        'subject',
        'last_message_at',
        'closed_at',
        'closed_by',
        'metadata',
    ];

    protected $casts = [
        'status' => ConversationStatus::class,
        'priority' => Priority::class,
        'last_message_at' => 'datetime',
        'closed_at' => 'datetime',
        'metadata' => 'array',
    ];

    protected $with = ['contact', 'channel'];

    public function contact(): BelongsTo
    {
        return $this->belongsTo(Contact::class);
    }

    public function channel(): BelongsTo
    {
        return $this->belongsTo(Channel::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function assignedAgent(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function closedByAgent(): BelongsTo
    {
        return $this->belongsTo(User::class, 'closed_by');
    }

    public function messages(): HasMany
    {
        return $this->hasMany(Message::class);
    }

    public function latestMessage(): BelongsTo
    {
        return $this->belongsTo(Message::class, 'latest_message_id');
    }

    // Scopes
    public function scopeOpen($query)
    {
        return $query->where('status', ConversationStatus::OPEN);
    }

    public function scopeAssigned($query)
    {
        return $query->where('status', ConversationStatus::ASSIGNED);
    }

    public function scopeClosed($query)
    {
        return $query->where('status', ConversationStatus::CLOSED);
    }

    public function scopeByPriority($query, Priority $priority)
    {
        return $query->where('priority', $priority);
    }

    public function scopeByAgent($query, int $agentId)
    {
        return $query->where('assigned_to', $agentId);
    }

    // Business Logic Methods
    public function isOpen(): bool
    {
        return $this->status === ConversationStatus::OPEN;
    }

    public function isAssigned(): bool
    {
        return $this->status === ConversationStatus::ASSIGNED;
    }

    public function isClosed(): bool
    {
        return $this->status === ConversationStatus::CLOSED;
    }

    public function canReceiveMessages(): bool
    {
        return in_array($this->status, [
            ConversationStatus::OPEN,
            ConversationStatus::ASSIGNED,
        ]);
    }

    public function isOld(): bool
    {
        return $this->last_message_at &&
               Carbon::parse($this->last_message_at)->diffInDays(now()) > 7;
    }
}
