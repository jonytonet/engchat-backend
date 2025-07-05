<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\MessageType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * 
 *
 * @property int $id
 * @property int $conversation_id
 * @property int|null $contact_id
 * @property int|null $user_id
 * @property MessageType $type
 * @property string|null $content
 * @property string|null $media_url
 * @property string|null $media_type
 * @property int|null $media_size
 * @property array<array-key, mixed>|null $metadata
 * @property string|null $external_id
 * @property \Illuminate\Support\Carbon|null $delivered_at
 * @property \Illuminate\Support\Carbon|null $read_at
 * @property bool $is_from_contact
 * @property string $message_status
 * @property int|null $reply_to_message_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property int|null $channel_id
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\MessageAttachment> $attachments
 * @property-read int|null $attachments_count
 * @property-read \App\Models\Contact|null $contact
 * @property-read \App\Models\Conversation $conversation
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Message> $replies
 * @property-read int|null $replies_count
 * @property-read Message|null $replyToMessage
 * @property-read \App\Models\User|null $user
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Message byType(\App\Enums\MessageType $type)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Message delivered()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Message fromAgent()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Message fromContact()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Message newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Message newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Message query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Message read()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Message whereChannelId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Message whereContactId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Message whereContent($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Message whereConversationId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Message whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Message whereDeliveredAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Message whereExternalId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Message whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Message whereIsFromContact($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Message whereMediaSize($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Message whereMediaType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Message whereMediaUrl($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Message whereMessageStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Message whereMetadata($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Message whereReadAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Message whereReplyToMessageId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Message whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Message whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Message whereUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Message withMedia()
 * @mixin \Eloquent
 */
class Message extends Model
{
    use HasFactory;

    protected $fillable = [
        'conversation_id',
        'contact_id',
        'user_id',
        'type',
        'content',
        'media_url',
        'media_type',
        'media_size',
        'metadata',
        'external_id',
        'delivered_at',
        'read_at',
        'is_from_contact',
        'reply_to_message_id',
    ];

    protected $casts = [
        'type' => MessageType::class,
        'metadata' => 'array',
        'delivered_at' => 'datetime',
        'read_at' => 'datetime',
        'is_from_contact' => 'boolean',
    ];

    public function conversation(): BelongsTo
    {
        return $this->belongsTo(Conversation::class);
    }

    public function contact(): BelongsTo
    {
        return $this->belongsTo(Contact::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function replyToMessage(): BelongsTo
    {
        return $this->belongsTo(Message::class, 'reply_to_message_id');
    }

    public function replies(): HasMany
    {
        return $this->hasMany(Message::class, 'reply_to_message_id');
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(MessageAttachment::class);
    }

    // Scopes
    public function scopeFromContact($query)
    {
        return $query->where('is_from_contact', true);
    }

    public function scopeFromAgent($query)
    {
        return $query->where('is_from_contact', false);
    }

    public function scopeByType($query, MessageType $type)
    {
        return $query->where('type', $type);
    }

    public function scopeWithMedia($query)
    {
        return $query->whereNotNull('media_url');
    }

    public function scopeDelivered($query)
    {
        return $query->whereNotNull('delivered_at');
    }

    public function scopeRead($query)
    {
        return $query->whereNotNull('read_at');
    }

}
