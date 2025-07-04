<?php

declare(strict_types=1);

namespace App\DTOs;

use App\Models\Message;

readonly class MessageDTO
{
    public function __construct(
        public int $id,
        public int $conversationId,
        public ?int $contactId,
        public ?int $userId,
        public string $type,
        public string $content,
        public ?string $mediaUrl,
        public ?string $mediaType,
        public ?int $mediaSize,
        public array $metadata,
        public ?string $externalId,
        public ?\DateTime $deliveredAt,
        public ?\DateTime $readAt,
        public bool $isFromContact,
        public ?int $replyToMessageId,
        public \DateTime $createdAt,
        public \DateTime $updatedAt
    ) {}

    public static function fromModel(Message $message): self
    {
        return new self(
            id: $message->id,
            conversationId: $message->conversation_id,
            contactId: $message->contact_id,
            userId: $message->user_id,
            type: $message->type->value,
            content: $message->content,
            mediaUrl: $message->media_url,
            mediaType: $message->media_type,
            mediaSize: $message->media_size,
            metadata: $message->metadata ?? [],
            externalId: $message->external_id,
            deliveredAt: $message->delivered_at,
            readAt: $message->read_at,
            isFromContact: $message->is_from_contact,
            replyToMessageId: $message->reply_to_message_id,
            createdAt: $message->created_at,
            updatedAt: $message->updated_at
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'conversation_id' => $this->conversationId,
            'contact_id' => $this->contactId,
            'user_id' => $this->userId,
            'type' => $this->type,
            'content' => $this->content,
            'media_url' => $this->mediaUrl,
            'media_type' => $this->mediaType,
            'media_size' => $this->mediaSize,
            'metadata' => $this->metadata,
            'external_id' => $this->externalId,
            'delivered_at' => $this->deliveredAt?->format('Y-m-d H:i:s'),
            'read_at' => $this->readAt?->format('Y-m-d H:i:s'),
            'is_from_contact' => $this->isFromContact,
            'reply_to_message_id' => $this->replyToMessageId,
            'created_at' => $this->createdAt->format('Y-m-d H:i:s'),
            'updated_at' => $this->updatedAt->format('Y-m-d H:i:s'),
        ];
    }
}
