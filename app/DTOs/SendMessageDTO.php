<?php

declare(strict_types=1);

namespace App\DTOs;

use App\Http\Requests\SendMessageRequest;

readonly class SendMessageDTO
{
    public function __construct(
        public int $conversationId,
        public ?int $contactId,
        public ?int $userId,
        public string $type,
        public string $content,
        public ?string $mediaUrl = null,
        public ?string $mediaType = null,
        public ?int $mediaSize = null,
        public array $metadata = [],
        public ?string $externalId = null,
        public bool $isFromContact = false,
        public ?int $replyToMessageId = null
    ) {}

    public static function fromRequest(SendMessageRequest $request, int $conversationId): self
    {
        return new self(
            conversationId: $conversationId,
            contactId: $request->validated('contact_id'),
            userId: $request->validated('user_id'),
            type: $request->validated('type', 'text'),
            content: $request->validated('content'),
            mediaUrl: $request->validated('media_url'),
            mediaType: $request->validated('media_type'),
            mediaSize: $request->validated('media_size'),
            metadata: $request->validated('metadata', []),
            externalId: $request->validated('external_id'),
            isFromContact: $request->validated('is_from_contact', false),
            replyToMessageId: $request->validated('reply_to_message_id')
        );
    }

    public function toArray(): array
    {
        return [
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
            'is_from_contact' => $this->isFromContact,
            'reply_to_message_id' => $this->replyToMessageId,
        ];
    }
}
