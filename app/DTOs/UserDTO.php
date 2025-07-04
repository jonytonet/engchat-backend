<?php

declare(strict_types=1);

namespace App\DTOs;

use App\Models\User;

readonly class UserDTO
{
    public function __construct(
        public int $id,
        public string $name,
        public string $email,
        public ?string $avatar,
        public string $status,
        public ?int $roleId,
        public ?int $departmentId,
        public ?int $managerId,
        public ?\DateTime $lastActivity,
        public string $timezone,
        public string $language,
        public bool $isActive,
        public \DateTime $createdAt,
        public \DateTime $updatedAt
    ) {}

    public static function fromModel(User $user): self
    {
        return new self(
            id: $user->id,
            name: $user->name,
            email: $user->email,
            avatar: $user->avatar,
            status: $user->status,
            roleId: $user->role_id,
            departmentId: $user->department_id,
            managerId: $user->manager_id,
            lastActivity: $user->last_activity,
            timezone: $user->timezone,
            language: $user->language,
            isActive: $user->is_active,
            createdAt: $user->created_at,
            updatedAt: $user->updated_at
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'avatar' => $this->avatar,
            'status' => $this->status,
            'role_id' => $this->roleId,
            'department_id' => $this->departmentId,
            'manager_id' => $this->managerId,
            'last_activity' => $this->lastActivity?->format('Y-m-d H:i:s'),
            'timezone' => $this->timezone,
            'language' => $this->language,
            'is_active' => $this->isActive,
            'created_at' => $this->createdAt->format('Y-m-d H:i:s'),
            'updated_at' => $this->updatedAt->format('Y-m-d H:i:s'),
        ];
    }
}
