<?php

declare(strict_types=1);

namespace App\DTOs;

use Carbon\Carbon;

/**
 * DTO for contact statistics data transfer.
 * 
 * Following DTO principles:
 * - Immutable data structure
 * - Type-safe properties
 * - NO business logic
 * - Only data transfer responsibility
 */
readonly class ContactStatsDTO
{
    public function __construct(
        public int $contactId,
        public int $totalConversations,
        public int $openConversations,
        public int $pendingConversations,
        public int $closedConversations,
        public int $totalMessages,
        public ?float $avgSatisfaction,
        public ?Carbon $lastInteraction,
        public int $totalInteractions,
        public bool $isVip,
        public bool $isBlacklisted
    ) {}

    /**
     * Convert to array for API responses.
     */
    public function toArray(): array
    {
        return [
            'contact_id' => $this->contactId,
            'total_conversations' => $this->totalConversations,
            'open_conversations' => $this->openConversations,
            'pending_conversations' => $this->pendingConversations,
            'closed_conversations' => $this->closedConversations,
            'total_messages' => $this->totalMessages,
            'avg_satisfaction' => $this->avgSatisfaction,
            'last_interaction' => $this->lastInteraction?->toISOString(),
            'total_interactions' => $this->totalInteractions,
            'is_vip' => $this->isVip,
            'is_blacklisted' => $this->isBlacklisted,
        ];
    }
}
