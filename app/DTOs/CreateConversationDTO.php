<?php

declare(strict_types=1);

namespace App\DTOs;

use App\Enums\ConversationStatus;
use App\Enums\Priority;
use App\Http\Requests\CreateConversationRequest;

readonly class CreateConversationDTO
{
    public function __construct(
        public int $contactId,
        public int $channelId,
        public ?int $categoryId = null,
        public ?int $assignedTo = null,
        public ConversationStatus $status = ConversationStatus::OPEN,
        public Priority $priority = Priority::MEDIUM,
        public array $tags = [],
        public bool $isBotHandled = false
    ) {}

    public static function fromRequest(CreateConversationRequest $request): self
    {
        return new self(
            contactId: $request->validated('contact_id'),
            channelId: $request->validated('channel_id'),
            categoryId: $request->validated('category_id'),
            assignedTo: $request->validated('assigned_to'),
            status: ConversationStatus::from($request->validated('status', 'open')),
            priority: Priority::from($request->validated('priority', 'medium')),
            tags: $request->validated('tags', []),
            isBotHandled: $request->validated('is_bot_handled', false)
        );
    }

    public function toArray(): array
    {
        return [
            'contact_id' => $this->contactId,
            'channel_id' => $this->channelId,
            'category_id' => $this->categoryId,
            'assigned_to' => $this->assignedTo,
            'status' => $this->status->value,
            'priority' => $this->priority->value,
            'tags' => $this->tags,
            'is_bot_handled' => $this->isBotHandled,
            'started_at' => now(),
        ];
    }
}
