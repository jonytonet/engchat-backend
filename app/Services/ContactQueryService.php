<?php

declare(strict_types=1);

namespace App\Services;

use App\DTOs\ContactDTO;
use App\Models\Contact;

/**
 * Service responsible for contact queries and lookups.
 * 
 * Following Single Responsibility Principle:
 * - ONLY handles contact query operations
 * - NO business logic beyond basic lookups
 * - NO data persistence operations
 */
final readonly class ContactQueryService
{
    /**
     * Find contact by business partner ID.
     */
    public function findByBusinessPartnerId(string $businessPartnerId): ?ContactDTO
    {
        $contact = Contact::where('businesspartner_id', $businessPartnerId)->first();

        return $contact ? ContactDTO::fromModel($contact) : null;
    }

    /**
     * Get contacts with business partner integration.
     */
    public function getContactsWithBusinessPartnerIntegration(): array
    {
        $contacts = Contact::whereNotNull('businesspartner_id')->get();

        return $contacts->map(fn($contact) => ContactDTO::fromModel($contact))->toArray();
    }

    /**
     * Check if business partner ID is already in use.
     */
    public function isBusinessPartnerIdInUse(string $businessPartnerId): bool
    {
        return Contact::where('businesspartner_id', $businessPartnerId)->exists();
    }

    /**
     * Find contact by phone number.
     */
    public function findByPhone(string $phoneNumber): ?Contact
    {
        return Contact::where('phone', $phoneNumber)->first();
    }

    /**
     * Find contact by phone number and return DTO.
     */
    public function findByPhoneAsDTO(string $phoneNumber): ?ContactDTO
    {
        $contact = $this->findByPhone($phoneNumber);

        return $contact ? ContactDTO::fromModel($contact) : null;
    }
}
