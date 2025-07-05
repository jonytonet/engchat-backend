<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\ConversationStatus;
use App\Enums\Priority;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * 
 *
 * @OA\Schema (
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
 * @property int $id
 * @property int $contact_id
 * @property int $channel_id
 * @property int|null $assigned_to
 * @property int|null $category_id
 * @property string|null $subject
 * @property ConversationStatus $status
 * @property Priority $priority
 * @property int|null $satisfaction_rating
 * @property string $started_at
 * @property \Illuminate\Support\Carbon|null $closed_at
 * @property int|null $first_response_time Tempo em segundos
 * @property int|null $resolution_time Tempo em segundos
 * @property string|null $tags
 * @property string|null $queue_entry_time
 * @property string|null $bot_handoff_time
 * @property string|null $first_human_response_time
 * @property int|null $current_queue_position
 * @property int $is_bot_handled
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\User|null $assignedAgent
 * @property-read \App\Models\Category|null $category
 * @property-read \App\Models\Channel $channel
 * @property-read \App\Models\User|null $closedByAgent
 * @property-read \App\Models\Contact $contact
 * @property-read \App\Models\Message|null $latestMessage
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Message> $messages
 * @property-read int|null $messages_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\ConversationTransfer> $transfers
 * @property-read int|null $transfers_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Conversation assigned()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Conversation byAgent(int $agentId)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Conversation byPriority(\App\Enums\Priority $priority)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Conversation closed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Conversation newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Conversation newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Conversation onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Conversation open()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Conversation query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Conversation whereAssignedTo($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Conversation whereBotHandoffTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Conversation whereCategoryId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Conversation whereChannelId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Conversation whereClosedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Conversation whereContactId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Conversation whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Conversation whereCurrentQueuePosition($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Conversation whereFirstHumanResponseTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Conversation whereFirstResponseTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Conversation whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Conversation whereIsBotHandled($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Conversation wherePriority($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Conversation whereQueueEntryTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Conversation whereResolutionTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Conversation whereSatisfactionRating($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Conversation whereStartedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Conversation whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Conversation whereSubject($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Conversation whereTags($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Conversation whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Conversation withTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Conversation withoutTrashed()
 * @mixin \Eloquent
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

    public function transfers(): HasMany
    {
        return $this->hasMany(ConversationTransfer::class);
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
}
