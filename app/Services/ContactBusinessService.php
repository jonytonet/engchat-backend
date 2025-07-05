<?php

declare(strict_types=1);

namespace App\Services;

use App\DTOs\ContactDTO;
use App\Models\Contact;
use Illuminate\Support\Facades\DB;

/**
 * Service responsible for contact business operations.
 * 
 * Following Single Responsibility Principle:
 * - ONLY handles contact business logic operations
 * - Maintains data consistency
 * - Implements business rules
 */
final readonly class ContactBusinessService
{
    /**
     * Update last interaction timestamp and increment counter.
     */
    public function updateLastInteraction(int $contactId): bool
    {
        return DB::transaction(function () use ($contactId) {
            $contact = Contact::findOrFail($contactId);

            return $contact->update([
                'last_interaction' => now(),
                'total_interactions' => $contact->total_interactions + 1,
            ]);
        });
    }

    /**
     * Add tag to contact with business validation.
     */
    public function addTag(int $contactId, string $tag): ContactDTO
    {
        $contact = Contact::findOrFail($contactId);

        // Business rule: Normalize tag name
        $tag = strtolower(trim($tag));

        // Business rule: Validate tag name
        if (empty($tag) || strlen($tag) > 50) {
            throw new \InvalidArgumentException('Tag must be between 1 and 50 characters');
        }

        $tags = $contact->tags ?? [];

        if (!in_array($tag, $tags)) {
            $tags[] = $tag;
            $contact->tags = $tags;
            $contact->save();
        }

        $contact->refresh();
        return ContactDTO::fromModel($contact);
    }

    /**
     * Remove tag from contact.
     */
    public function removeTag(int $contactId, string $tag): ContactDTO
    {
        $contact = Contact::findOrFail($contactId);

        if (!$contact->tags) {
            return ContactDTO::fromModel($contact);
        }

        $tag = strtolower(trim($tag));

        $tags = array_filter($contact->tags, function ($t) use ($tag) {
            return strtolower($t) !== $tag;
        });

        $contact->tags = array_values($tags);
        $contact->save();

        $contact->refresh();
        return ContactDTO::fromModel($contact);
    }

    /**
     * Blacklist contact with reason.
     */
    public function blacklistContact(int $contactId, string $reason): ContactDTO
    {
        $contact = Contact::findOrFail($contactId);

        // Business rule: Validate blacklist reason
        if (empty(trim($reason))) {
            throw new \InvalidArgumentException('Blacklist reason is required');
        }

        $contact->update([
            'blacklisted' => true,
            'blacklist_reason' => $reason,
        ]);

        $contact->refresh();
        return ContactDTO::fromModel($contact);
    }

    /**
     * Remove contact from blacklist.
     */
    public function removeFromBlacklist(int $contactId): ContactDTO
    {
        $contact = Contact::findOrFail($contactId);

        $contact->update([
            'blacklisted' => false,
            'blacklist_reason' => null,
        ]);

        $contact->refresh();
        return ContactDTO::fromModel($contact);
    }

    /**
     * Update contact priority with business validation.
     */
    public function updatePriority(int $contactId, string $priority): ContactDTO
    {
        $validPriorities = ['low', 'medium', 'high', 'urgent'];

        if (!in_array($priority, $validPriorities)) {
            throw new \InvalidArgumentException('Priority must be one of: ' . implode(', ', $validPriorities));
        }

        $contact = Contact::findOrFail($contactId);
        $contact->update(['priority' => $priority]);

        $contact->refresh();
        return ContactDTO::fromModel($contact);
    }
}
