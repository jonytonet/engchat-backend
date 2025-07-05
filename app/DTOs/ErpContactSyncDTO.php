<?php

declare(strict_types=1);

namespace App\DTOs;

readonly class ErpContactSyncDTO
{
    public function __construct(
        public string $businessPartnerId,
        public ?string $name = null,
        public ?string $email = null,
        public ?string $phone = null,
        public ?string $company = null,
        public ?string $document = null,
        public ?string $priority = null,
        public array $tags = [],
        public array $metadata = []
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            businessPartnerId: $data['businesspartner_id'],
            name: $data['name'] ?? null,
            email: $data['email'] ?? null,
            phone: $data['phone'] ?? null,
            company: $data['company'] ?? null,
            document: $data['document'] ?? null,
            priority: $data['priority'] ?? null,
            tags: $data['tags'] ?? [],
            metadata: $data['metadata'] ?? []
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'businesspartner_id' => $this->businessPartnerId,
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
            'company' => $this->company,
            'document' => $this->document,
            'priority' => $this->priority,
            'tags' => $this->tags,
            'metadata' => $this->metadata,
        ], fn($value) => $value !== null && $value !== []);
    }
}
