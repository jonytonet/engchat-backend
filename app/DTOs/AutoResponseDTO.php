<?php

declare(strict_types=1);

namespace App\DTOs;

use App\Models\AutoResponse;

readonly class AutoResponseDTO
{
    public function __construct(
        public int $id,
        public ?int $categoryId,
        public string $name,
        public string $triggerType,
        public ?string $triggerKeyword,
        public ?array $triggerConditions,
        public string $responseText,
        public string $responseType,
        public ?array $responseData,
        public int $delaySeconds,
        public ?array $workingHours,
        public ?array $conditions,
        public bool $isActive,
        public int $usageCount,
        public int $priority,
        public \DateTime $createdAt,
        public \DateTime $updatedAt
    ) {}

    public static function fromModel(AutoResponse $autoResponse): self
    {
        return new self(
            id: $autoResponse->id,
            categoryId: $autoResponse->category_id,
            name: $autoResponse->name,
            triggerType: $autoResponse->trigger_type,
            triggerKeyword: $autoResponse->trigger_keyword,
            triggerConditions: $autoResponse->trigger_conditions,
            responseText: $autoResponse->response_text,
            responseType: $autoResponse->response_type,
            responseData: $autoResponse->response_data,
            delaySeconds: $autoResponse->delay_seconds,
            workingHours: $autoResponse->working_hours,
            conditions: $autoResponse->conditions,
            isActive: $autoResponse->is_active,
            usageCount: $autoResponse->usage_count,
            priority: $autoResponse->priority,
            createdAt: $autoResponse->created_at,
            updatedAt: $autoResponse->updated_at
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'category_id' => $this->categoryId,
            'name' => $this->name,
            'trigger_type' => $this->triggerType,
            'trigger_keyword' => $this->triggerKeyword,
            'trigger_conditions' => $this->triggerConditions,
            'response_text' => $this->responseText,
            'response_type' => $this->responseType,
            'response_data' => $this->responseData,
            'delay_seconds' => $this->delaySeconds,
            'working_hours' => $this->workingHours,
            'conditions' => $this->conditions,
            'is_active' => $this->isActive,
            'usage_count' => $this->usageCount,
            'priority' => $this->priority,
            'created_at' => $this->createdAt->format('Y-m-d H:i:s'),
            'updated_at' => $this->updatedAt->format('Y-m-d H:i:s'),
        ];
    }
}
