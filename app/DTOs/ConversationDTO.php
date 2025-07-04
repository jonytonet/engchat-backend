<?php

declare(strict_types=1);

namespace App\DTOs;

use App\Enums\ConversationStatus;
use App\Enums\Priority;
use App\Models\Conversation;

readonly class ConversationDTO
{
    public function __construct(
        public int $id,
        public int $contactId,
        public int $channelId,
        public ?int $categoryId,
        public ?int $assignedTo,
        public ConversationStatus $status,
        public Priority $priority,
        public ?int $satisfactionRating,
        public \DateTime $startedAt,
        public ?\DateTime $closedAt,
        public ?int $firstResponseTime,
        public ?int $resolutionTime,
        public array $tags,
        public bool $isBotHandled,
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
            status: $conversation->status,
            priority: $conversation->priority,
            satisfactionRating: $conversation->satisfaction_rating,
            startedAt: $conversation->started_at,
            closedAt: $conversation->closed_at,
            firstResponseTime: $conversation->first_response_time,
            resolutionTime: $conversation->resolution_time,
            tags: $conversation->tags ?? [],
            isBotHandled: $conversation->is_bot_handled,
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
            'status' => $this->status->value,
            'status_label' => $this->status->label(),
            'priority' => $this->priority->value,
            'priority_label' => $this->priority->label(),
            'satisfaction_rating' => $this->satisfactionRating,
            'started_at' => $this->startedAt->format('Y-m-d H:i:s'),
            'closed_at' => $this->closedAt?->format('Y-m-d H:i:s'),
            'first_response_time' => $this->firstResponseTime,
            'resolution_time' => $this->resolutionTime,
            'tags' => $this->tags,
            'is_bot_handled' => $this->isBotHandled,
            'created_at' => $this->createdAt->format('Y-m-d H:i:s'),
            'updated_at' => $this->updatedAt->format('Y-m-d H:i:s'),
        ];
    }
}
