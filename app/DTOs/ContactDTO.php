<?php

declare(strict_types=1);

namespace App\DTOs;

use App\Enums\Priority;
use App\Models\Contact;

readonly class ContactDTO
{
    public function __construct(
        public int $id,
        public string $name,
        public ?string $email,
        public ?string $phone,
        public ?string $displayName,
        public ?string $company,
        public ?string $document,
        public array $tags,
        public Priority $priority,
        public bool $blacklisted,
        public ?string $blacklistReason,
        public string $preferredLanguage,
        public string $timezone,
        public ?\DateTime $lastInteraction,
        public int $totalInteractions,
        public ?string $businessPartnerId,
        public \DateTime $createdAt,
        public \DateTime $updatedAt,
        public ?\DateTime $deletedAt = null
    ) {}

    public static function fromModel(Contact $contact): self
    {
        return new self(
            id: $contact->id,
            name: $contact->name,
            email: $contact->email,
            phone: $contact->phone,
            displayName: $contact->display_name,
            company: $contact->company,
            document: $contact->document,
            tags: $contact->tags ?? [],
            priority: Priority::from($contact->priority),
            blacklisted: $contact->blacklisted,
            blacklistReason: $contact->blacklist_reason,
            preferredLanguage: $contact->preferred_language,
            timezone: $contact->timezone,
            lastInteraction: $contact->last_interaction,
            totalInteractions: $contact->total_interactions,
            businessPartnerId: $contact->businesspartner_id,
            createdAt: $contact->created_at,
            updatedAt: $contact->updated_at,
            deletedAt: $contact->deleted_at
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
            'display_name' => $this->displayName,
            'company' => $this->company,
            'document' => $this->document,
            'tags' => $this->tags,
            'priority' => $this->priority->value,
            'priority_label' => $this->priority->label(),
            'blacklisted' => $this->blacklisted,
            'blacklist_reason' => $this->blacklistReason,
            'preferred_language' => $this->preferredLanguage,
            'timezone' => $this->timezone,
            'last_interaction' => $this->lastInteraction?->format('Y-m-d H:i:s'),
            'total_interactions' => $this->totalInteractions,
            'businesspartner_id' => $this->businessPartnerId,
            'created_at' => $this->createdAt->format('Y-m-d H:i:s'),
            'updated_at' => $this->updatedAt->format('Y-m-d H:i:s'),
            'deleted_at' => $this->deletedAt?->format('Y-m-d H:i:s'),
        ];
    }
}
