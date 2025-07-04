<?php

declare(strict_types=1);

namespace App\DTOs;

use App\Enums\ConversationStatus;
use App\Enums\Priority;

readonly class ConversationWithRelationsDTO
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
        public \DateTime $updatedAt,
        // Relations data
        public ?array $contactData = null,
        public ?array $channelData = null,
        public ?array $categoryData = null,
        public ?array $assignedAgentData = null,
        public array $messagesData = [],
        public ?array $latestMessageData = null
    ) {}

    public static function fromArrays(array $conversationData, array $relations = []): self
    {
        return new self(
            id: $conversationData['id'],
            contactId: $conversationData['contact_id'],
            channelId: $conversationData['channel_id'],
            categoryId: $conversationData['category_id'],
            assignedTo: $conversationData['assigned_to'],
            status: ConversationStatus::from($conversationData['status']),
            priority: Priority::from($conversationData['priority']),
            satisfactionRating: $conversationData['satisfaction_rating'],
            startedAt: new \DateTime($conversationData['started_at']),
            closedAt: $conversationData['closed_at'] ? new \DateTime($conversationData['closed_at']) : null,
            firstResponseTime: $conversationData['first_response_time'],
            resolutionTime: $conversationData['resolution_time'],
            tags: $conversationData['tags'] ?? [],
            isBotHandled: $conversationData['is_bot_handled'],
            createdAt: new \DateTime($conversationData['created_at']),
            updatedAt: new \DateTime($conversationData['updated_at']),
            // Relations
            contactData: $relations['contact'] ?? null,
            channelData: $relations['channel'] ?? null,
            categoryData: $relations['category'] ?? null,
            assignedAgentData: $relations['assignedAgent'] ?? null,
            messagesData: $relations['messages'] ?? [],
            latestMessageData: $relations['latestMessage'] ?? null,
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
            // Relations
            'contact' => $this->contactData,
            'channel' => $this->channelData,
            'category' => $this->categoryData,
            'assigned_agent' => $this->assignedAgentData,
            'messages' => $this->messagesData,
            'latest_message' => $this->latestMessageData,
        ];
    }
}
