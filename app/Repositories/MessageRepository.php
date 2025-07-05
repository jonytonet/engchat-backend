<?php

namespace App\Repositories;

use App\Models\Message;
use Illuminate\Container\Container as Application;

class MessageRepository extends BaseRepository
{
    public function __construct(Application $app)
    {
        parent::__construct($app);
    }

    /**
     * Configure the Model
     */
    public function model(): string
    {
        return Message::class;
    }

    /**
     * Get searchable fields array
     */
    public function getFieldsSearchable(): array
    {
        return [
            'conversation_id',
            'contact_id',
            'user_id',
            'content',
            'type',
            'is_from_contact'
        ];
    }

    /**
     * Get messages by conversation with relations
     */
    public function getByConversationWithRelations(int $conversationId, int $limit = 50): \Illuminate\Database\Eloquent\Collection
    {
        return $this->model
            ->where('conversation_id', $conversationId)
            ->with(['contact', 'user', 'attachments'])
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Find by WhatsApp message ID
     */
    public function findByWhatsAppId(string $whatsappId): ?Message
    {
        return $this->model
            ->whereJsonContains('metadata->whatsapp_message_id', $whatsappId)
            ->first();
    }

    /**
     * Get unread count for conversation
     */
    public function getUnreadCountByConversation(int $conversationId): int
    {
        return $this->model
            ->where('conversation_id', $conversationId)
            ->whereNull('read_at')
            ->where('is_from_contact', true)
            ->count();
    }

    /**
     * Mark messages as read for conversation
     */
    public function markConversationAsRead(int $conversationId): int
    {
        return $this->model
            ->where('conversation_id', $conversationId)
            ->whereNull('read_at')
            ->where('is_from_contact', true)
            ->update(['read_at' => now()]);
    }
}
