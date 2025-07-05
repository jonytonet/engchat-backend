<?php

namespace App\Services;

use App\DTOs\MessageDTO;
use App\DTOs\SendMessageDTO;
use App\Models\Message;
use App\Repositories\MessageRepository;
use Illuminate\Database\Eloquent\Collection;

class MessageService
{
    public function __construct(
        private MessageRepository $messageRepository
    ) {}

    /**
     * Create a new message
     */
    public function createMessage(SendMessageDTO $dto): MessageDTO
    {
        $data = [
            'conversation_id' => $dto->conversationId,
            'contact_id' => $dto->contactId,
            'content' => $dto->content,
            'type' => $dto->type,
            'metadata' => $dto->metadata,
            'is_from_contact' => $dto->isFromContact ?? true,
        ];

        // Add user_id only if provided
        if ($dto->userId) {
            $data['user_id'] = $dto->userId;
        }

        $message = Message::create($data);

        return MessageDTO::fromModel($message);
    }

    /**
     * Find message by WhatsApp ID
     */
    public function findByWhatsAppId(string $whatsappId): ?MessageDTO
    {
        $message = Message::whereJsonContains('metadata->whatsapp_message_id', $whatsappId)->first();

        return $message ? MessageDTO::fromModel($message) : null;
    }

    /**
     * Update message delivery status
     */
    public function updateDeliveryStatus(int $messageId, string $status): MessageDTO
    {
        $message = Message::findOrFail($messageId);

        $updateData = [
            'metadata' => array_merge(
                $message->metadata ?? [],
                ['whatsapp_status' => $status]
            )
        ];

        // Update specific timestamp fields based on status
        switch ($status) {
            case 'delivered':
                $updateData['delivered_at'] = now();
                break;
            case 'read':
                $updateData['read_at'] = now();
                break;
        }

        $message->update($updateData);

        return MessageDTO::fromModel($message);
    }

    /**
     * Get messages for conversation
     */
    public function getByConversation(int $conversationId, int $limit = 50): Collection
    {
        return Message::where('conversation_id', $conversationId)
            ->with(['contact', 'user', 'attachments'])
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Mark message as read
     */
    public function markAsRead(int $messageId): MessageDTO
    {
        $message = Message::findOrFail($messageId);
        $message->update(['read_at' => now()]);

        return MessageDTO::fromModel($message);
    }

    /**
     * Get unread messages count for conversation
     */
    public function getUnreadCount(int $conversationId): int
    {
        return Message::where('conversation_id', $conversationId)
            ->whereNull('read_at')
            ->where('is_from_contact', true)
            ->count();
    }
}
