<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\MessageType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Message extends Model
{
    use HasFactory, SoftDeletes;

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

    // Business Logic Methods
    public function isFromContact(): bool
    {
        return $this->is_from_contact;
    }

    public function isFromAgent(): bool
    {
        return !$this->is_from_contact;
    }

    public function hasMedia(): bool
    {
        return !empty($this->media_url);
    }

    public function isDelivered(): bool
    {
        return !is_null($this->delivered_at);
    }

    public function isRead(): bool
    {
        return !is_null($this->read_at);
    }

    public function isReply(): bool
    {
        return !is_null($this->reply_to_message_id);
    }

    public function getDisplayContent(): string
    {
        return match($this->type) {
            MessageType::TEXT => $this->content,
            MessageType::IMAGE => '📷 Imagem',
            MessageType::VIDEO => '🎥 Vídeo',
            MessageType::AUDIO => '🎵 Áudio',
            MessageType::FILE => '📎 Arquivo',
            MessageType::LOCATION => '📍 Localização',
            MessageType::CONTACT => '👤 Contato',
            MessageType::STICKER => '😀 Figurinha',
            MessageType::TEMPLATE => '📋 Template',
            MessageType::SYSTEM => $this->content,
        };
    }
}
