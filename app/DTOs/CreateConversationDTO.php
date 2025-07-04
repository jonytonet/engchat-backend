<?php

declare(strict_types=1);

namespace App\DTOs;

use App\Http\Requests\CreateConversationRequest;
use App\Models\Conversation;

readonly class CreateConversationDTO
{
    public function __construct(
        public int $contactId,
        public int $channelId,
        public ?int $categoryId = null,
        public string $priority = 'normal'
    ) {}

    public static function fromRequest(CreateConversationRequest $request): self
    {
        return new self(
            contactId: $request->validated('contact_id'),
            channelId: $request->validated('channel_id'),
            categoryId: $request->validated('category_id'),
            priority: $request->validated('priority', 'normal')
        );
    }

    public function toArray(): array
    {
        return [
            'contact_id' => $this->contactId,
            'channel_id' => $this->channelId,
            'category_id' => $this->categoryId,
            'priority' => $this->priority,
            'status' => 'open',
            'last_message_at' => now(),
        ];
    }
}
