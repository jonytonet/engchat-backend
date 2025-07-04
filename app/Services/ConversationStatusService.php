<?php

namespace App\Services;

use App\Models\Conversation;
use App\Enums\ConversationStatus;
use Carbon\Carbon;

class ConversationStatusService
{
    /**
     * Check if conversation is open.
     */
    public function isOpen(Conversation $conversation): bool
    {
        return $conversation->status === ConversationStatus::OPEN;
    }

    /**
     * Check if conversation is assigned.
     */
    public function isAssigned(Conversation $conversation): bool
    {
        return $conversation->status === ConversationStatus::ASSIGNED;
    }

    /**
     * Check if conversation is closed.
     */
    public function isClosed(Conversation $conversation): bool
    {
        return $conversation->status === ConversationStatus::CLOSED;
    }

    /**
     * Check if conversation can receive new messages.
     */
    public function canReceiveMessages(Conversation $conversation): bool
    {
        return in_array($conversation->status, [
            ConversationStatus::OPEN,
            ConversationStatus::ASSIGNED,
        ]);
    }

    /**
     * Check if conversation is considered old (no activity for 7+ days).
     */
    public function isOld(Conversation $conversation): bool
    {
        return $conversation->last_message_at &&
               Carbon::parse($conversation->last_message_at)->diffInDays(now()) > 7;
    }

    /**
     * Update conversation status.
     */
    public function updateStatus(Conversation $conversation, ConversationStatus $status): void
    {
        $conversation->status = $status;

        if ($status === ConversationStatus::CLOSED) {
            $conversation->closed_at = now();
        }

        $conversation->save();
    }

    /**
     * Check if conversation requires urgent attention.
     */
    public function requiresUrgentAttention(Conversation $conversation): bool
    {
        // High priority conversations that are unassigned
        if ($conversation->priority->value === 'urgent' && !$conversation->assigned_to) {
            return true;
        }

        // Conversations without response for too long
        if ($this->isOld($conversation) && $this->canReceiveMessages($conversation)) {
            return true;
        }

        return false;
    }

    /**
     * Get status label for display.
     */
    public function getStatusLabel(Conversation $conversation): string
    {
        return match($conversation->status) {
            ConversationStatus::OPEN => 'Aberta',
            ConversationStatus::ASSIGNED => 'Atribuída',
            ConversationStatus::CLOSED => 'Fechada',
            default => 'Desconhecido',
        };
    }

    /**
     * Get possible next statuses for a conversation.
     */
    public function getPossibleNextStatuses(Conversation $conversation): array
    {
        return match($conversation->status) {
            ConversationStatus::OPEN => [ConversationStatus::ASSIGNED, ConversationStatus::CLOSED],
            ConversationStatus::ASSIGNED => [ConversationStatus::OPEN, ConversationStatus::CLOSED],
            ConversationStatus::CLOSED => [ConversationStatus::OPEN],
            default => [],
        };
    }
}
