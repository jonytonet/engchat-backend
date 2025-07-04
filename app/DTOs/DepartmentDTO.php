<?php

declare(strict_types=1);

namespace App\DTOs;

use App\Models\Department;

readonly class DepartmentDTO
{
    public function __construct(
        public int $id,
        public string $name,
        public ?string $description,
        public ?int $managerId,
        public bool $isActive,
        public ?array $workingHours,
        public bool $autoAssignmentEnabled,
        public \DateTime $createdAt,
        public \DateTime $updatedAt
    ) {}

    public static function fromModel(Department $department): self
    {
        return new self(
            id: $department->id,
            name: $department->name,
            description: $department->description,
            managerId: $department->manager_id,
            isActive: $department->is_active,
            workingHours: $department->working_hours,
            autoAssignmentEnabled: $department->auto_assignment_enabled,
            createdAt: $department->created_at,
            updatedAt: $department->updated_at
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'manager_id' => $this->managerId,
            'is_active' => $this->isActive,
            'working_hours' => $this->workingHours,
            'auto_assignment_enabled' => $this->autoAssignmentEnabled,
            'created_at' => $this->createdAt->format('Y-m-d H:i:s'),
            'updated_at' => $this->updatedAt->format('Y-m-d H:i:s'),
        ];
    }
}
