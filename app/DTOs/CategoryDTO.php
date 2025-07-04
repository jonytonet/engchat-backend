<?php

declare(strict_types=1);

namespace App\DTOs;

use App\Models\Category;

readonly class CategoryDTO
{
    public function __construct(
        public int $id,
        public string $name,
        public ?string $description,
        public string $color,
        public ?int $parentId,
        public int $priority,
        public ?int $estimatedTime,
        public ?array $autoResponses,
        public bool $requiresSpecialist,
        public bool $isActive,
        public \DateTime $createdAt,
        public \DateTime $updatedAt
    ) {}

    public static function fromModel(Category $category): self
    {
        return new self(
            id: $category->id,
            name: $category->name,
            description: $category->description,
            color: $category->color,
            parentId: $category->parent_id,
            priority: $category->priority,
            estimatedTime: $category->estimated_time,
            autoResponses: $category->auto_responses,
            requiresSpecialist: $category->requires_specialist,
            isActive: $category->is_active,
            createdAt: $category->created_at,
            updatedAt: $category->updated_at
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'color' => $this->color,
            'parent_id' => $this->parentId,
            'priority' => $this->priority,
            'estimated_time' => $this->estimatedTime,
            'auto_responses' => $this->autoResponses,
            'requires_specialist' => $this->requiresSpecialist,
            'is_active' => $this->isActive,
            'created_at' => $this->createdAt->format('Y-m-d H:i:s'),
            'updated_at' => $this->updatedAt->format('Y-m-d H:i:s'),
        ];
    }
}
