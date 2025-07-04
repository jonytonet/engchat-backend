<?php

declare(strict_types=1);

namespace App\DTOs;

use App\Models\Role;

readonly class RoleDTO
{
    public function __construct(
        public int $id,
        public string $name,
        public ?string $description,
        public ?array $permissions,
        public bool $canTransfer,
        public bool $canCloseTickets,
        public int $maxSimultaneousChats,
        public \DateTime $createdAt,
        public \DateTime $updatedAt
    ) {}

    public static function fromModel(Role $role): self
    {
        return new self(
            id: $role->id,
            name: $role->name,
            description: $role->description,
            permissions: $role->permissions,
            canTransfer: $role->can_transfer,
            canCloseTickets: $role->can_close_tickets,
            maxSimultaneousChats: $role->max_simultaneous_chats,
            createdAt: $role->created_at,
            updatedAt: $role->updated_at
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'permissions' => $this->permissions,
            'can_transfer' => $this->canTransfer,
            'can_close_tickets' => $this->canCloseTickets,
            'max_simultaneous_chats' => $this->maxSimultaneousChats,
            'created_at' => $this->createdAt->format('Y-m-d H:i:s'),
            'updated_at' => $this->updatedAt->format('Y-m-d H:i:s'),
        ];
    }
}
