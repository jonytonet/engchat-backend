<?php

declare(strict_types=1);

namespace App\DTOs;

use App\Models\Contact;

readonly class ContactDTO
{
    public function __construct(
        public int $id,
        public string $name,
        public ?string $email,
        public ?string $phone,
        public ?string $document,
        public ?string $avatar,
        public ?string $timezone,
        public ?string $language,
        public array $metadata,
        public bool $isBlocked,
        public ?string $blockedReason,
        public ?\DateTime $blockedAt,
        public ?int $blockedBy,
        public \DateTime $createdAt,
        public \DateTime $updatedAt
    ) {}

    public static function fromModel(Contact $contact): self
    {
        return new self(
            id: $contact->id,
            name: $contact->name,
            email: $contact->email,
            phone: $contact->phone,
            document: $contact->document,
            avatar: $contact->avatar,
            timezone: $contact->timezone,
            language: $contact->language,
            metadata: $contact->metadata ?? [],
            isBlocked: $contact->is_blocked,
            blockedReason: $contact->blocked_reason,
            blockedAt: $contact->blocked_at,
            blockedBy: $contact->blocked_by,
            createdAt: $contact->created_at,
            updatedAt: $contact->updated_at
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
            'document' => $this->document,
            'avatar' => $this->avatar,
            'timezone' => $this->timezone,
            'language' => $this->language,
            'metadata' => $this->metadata,
            'is_blocked' => $this->isBlocked,
            'blocked_reason' => $this->blockedReason,
            'blocked_at' => $this->blockedAt?->format('Y-m-d H:i:s'),
            'blocked_by' => $this->blockedBy,
            'created_at' => $this->createdAt->format('Y-m-d H:i:s'),
            'updated_at' => $this->updatedAt->format('Y-m-d H:i:s'),
        ];
    }
}
