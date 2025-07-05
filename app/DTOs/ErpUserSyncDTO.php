<?php

declare(strict_types=1);

namespace App\DTOs;

readonly class ErpUserSyncDTO
{
    public function __construct(
        public string $erpUserId,
        public ?string $name = null,
        public ?string $email = null,
        public ?int $departmentId = null,
        public ?string $status = null,
        public ?bool $isActive = null,
        public array $metadata = []
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            erpUserId: $data['erp_user_id'],
            name: $data['name'] ?? null,
            email: $data['email'] ?? null,
            departmentId: $data['department_id'] ?? null,
            status: $data['status'] ?? null,
            isActive: $data['is_active'] ?? null,
            metadata: $data['metadata'] ?? []
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'erp_user_id' => $this->erpUserId,
            'name' => $this->name,
            'email' => $this->email,
            'department_id' => $this->departmentId,
            'status' => $this->status,
            'is_active' => $this->isActive,
            'metadata' => $this->metadata,
        ], fn($value) => $value !== null);
    }
}
