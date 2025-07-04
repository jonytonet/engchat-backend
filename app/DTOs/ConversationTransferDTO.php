<?php

declare(strict_types=1);

namespace App\DTOs;

use App\Models\ConversationTransfer;

readonly class ConversationTransferDTO
{
    public function __construct(
        public int $id,
        public int $conversationId,
        public ?int $fromUserId,
        public int $toUserId,
        public string $reason,
        public ?string $notes,
        public string $status,
        public \DateTime $transferredAt,
        public ?\DateTime $acceptedAt,
        public \DateTime $createdAt,
        public \DateTime $updatedAt
    ) {}

    public static function fromModel(ConversationTransfer $transfer): self
    {
        return new self(
            id: $transfer->id,
            conversationId: $transfer->conversation_id,
            fromUserId: $transfer->from_user_id,
            toUserId: $transfer->to_user_id,
            reason: $transfer->reason,
            notes: $transfer->notes,
            status: $transfer->status,
            transferredAt: $transfer->transferred_at,
            acceptedAt: $transfer->accepted_at,
            createdAt: $transfer->created_at,
            updatedAt: $transfer->updated_at
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'conversation_id' => $this->conversationId,
            'from_user_id' => $this->fromUserId,
            'to_user_id' => $this->toUserId,
            'reason' => $this->reason,
            'notes' => $this->notes,
            'status' => $this->status,
            'transferred_at' => $this->transferredAt->format('Y-m-d H:i:s'),
            'accepted_at' => $this->acceptedAt?->format('Y-m-d H:i:s'),
            'created_at' => $this->createdAt->format('Y-m-d H:i:s'),
            'updated_at' => $this->updatedAt->format('Y-m-d H:i:s'),
        ];
    }
}
