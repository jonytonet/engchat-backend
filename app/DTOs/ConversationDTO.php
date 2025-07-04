<?php

declare(strict_types=1);

namespace App\DTOs;

use App\Models\Conversation;

readonly class ConversationDTO
{
    public function __construct(
        public int $id,
        public int $contactId,
        public int $channelId,
        public ?int $categoryId,
        public ?int $assignedTo,
        public string $status,
        public string $priority,
        public ?string $subject,
        public ?\DateTime $lastMessageAt,
        public ?\DateTime $closedAt,
        public ?int $closedBy,
        public array $metadata,
        public \DateTime $createdAt,
        public \DateTime $updatedAt
    ) {}

    public static function fromModel(Conversation $conversation): self
    {
        return new self(
            id: $conversation->id,
            contactId: $conversation->contact_id,
            channelId: $conversation->channel_id,
            categoryId: $conversation->category_id,
            assignedTo: $conversation->assigned_to,
            status: $conversation->status->value,
            priority: $conversation->priority->value,
            subject: $conversation->subject,
            lastMessageAt: $conversation->last_message_at,
            closedAt: $conversation->closed_at,
            closedBy: $conversation->closed_by,
            metadata: $conversation->metadata ?? [],
            createdAt: $conversation->created_at,
            updatedAt: $conversation->updated_at
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'contact_id' => $this->contactId,
            'channel_id' => $this->channelId,
            'category_id' => $this->categoryId,
            'assigned_to' => $this->assignedTo,
            'status' => $this->status,
            'priority' => $this->priority,
            'subject' => $this->subject,
            'last_message_at' => $this->lastMessageAt?->format('Y-m-d H:i:s'),
            'closed_at' => $this->closedAt?->format('Y-m-d H:i:s'),
            'closed_by' => $this->closedBy,
            'metadata' => $this->metadata,
            'created_at' => $this->createdAt->format('Y-m-d H:i:s'),
            'updated_at' => $this->updatedAt->format('Y-m-d H:i:s'),
        ];
    }
}
