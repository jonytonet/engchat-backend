<?php

declare(strict_types=1);

namespace App\Services;

use App\DTOs\ContactDTO;
use App\DTOs\ContactStatsDTO;
use App\Models\Contact;

/**
 * Service responsible for contact statistics and analytics.
 * 
 * Following Single Responsibility Principle:
 * - ONLY handles contact statistics calculations
 * - NO data persistence operations
 * - Returns structured DTOs
 */
final readonly class ContactStatsService
{
    /**
     * Get comprehensive statistics for a contact.
     */
    public function getContactStats(int $contactId): ContactStatsDTO
    {
        $contact = Contact::with(['conversations', 'messages'])
            ->findOrFail($contactId);

        $totalConversations = $contact->conversations()->count();
        $openConversations = $contact->conversations()->where('status', 'open')->count();
        $pendingConversations = $contact->conversations()->where('status', 'pending')->count();
        $closedConversations = $contact->conversations()->where('status', 'closed')->count();
        $totalMessages = $contact->messages()->count();

        $avgSatisfaction = $contact->conversations()
            ->whereNotNull('satisfaction_rating')
            ->avg('satisfaction_rating');

        return new ContactStatsDTO(
            contactId: $contactId,
            totalConversations: $totalConversations,
            openConversations: $openConversations,
            pendingConversations: $pendingConversations,
            closedConversations: $closedConversations,
            totalMessages: $totalMessages,
            avgSatisfaction: $avgSatisfaction ? round($avgSatisfaction, 2) : null,
            lastInteraction: $contact->last_interaction,
            totalInteractions: $contact->total_interactions,
            isVip: $contact->isVip(),
            isBlacklisted: $contact->isBlacklisted()
        );
    }

    /**
     * Get contact engagement metrics.
     */
    public function getEngagementMetrics(int $contactId): array
    {
        $contact = Contact::findOrFail($contactId);

        $avgResponseTime = $contact->conversations()
            ->whereNotNull('first_response_time')
            ->avg('first_response_time');

        $avgResolutionTime = $contact->conversations()
            ->whereNotNull('resolution_time')
            ->avg('resolution_time');

        return [
            'avg_response_time_minutes' => $avgResponseTime ? round($avgResponseTime / 60, 2) : null,
            'avg_resolution_time_hours' => $avgResolutionTime ? round($avgResolutionTime / 3600, 2) : null,
            'interaction_frequency_days' => $this->calculateInteractionFrequency($contact),
            'preferred_contact_method' => $contact->getPreferredContactMethod(),
        ];
    }

    /**
     * Calculate average days between interactions.
     */
    private function calculateInteractionFrequency(Contact $contact): ?float
    {
        if ($contact->total_interactions <= 1) {
            return null;
        }

        $firstInteraction = $contact->created_at;
        $lastInteraction = $contact->last_interaction ?? now();

        $totalDays = $firstInteraction->diffInDays($lastInteraction);

        return $totalDays > 0 ? round($totalDays / $contact->total_interactions, 1) : null;
    }
}
