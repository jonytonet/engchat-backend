<?php

declare(strict_types=1);

namespace App\DTOs;

use App\Models\Channel;

readonly class ChannelDTO
{
    public function __construct(
        public int $id,
        public string $name,
        public string $type,
        public array $configuration,
        public bool $isActive,
        public int $priority,
        public ?array $workingHours,
        public bool $autoResponseEnabled,
        public \DateTime $createdAt,
        public \DateTime $updatedAt
    ) {}

    public static function fromModel(Channel $channel): self
    {
        return new self(
            id: $channel->id,
            name: $channel->name,
            type: $channel->type,
            configuration: $channel->configuration ?? [],
            isActive: $channel->is_active,
            priority: $channel->priority,
            workingHours: $channel->working_hours,
            autoResponseEnabled: $channel->auto_response_enabled,
            createdAt: $channel->created_at,
            updatedAt: $channel->updated_at
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'type' => $this->type,
            'configuration' => $this->configuration,
            'is_active' => $this->isActive,
            'priority' => $this->priority,
            'working_hours' => $this->workingHours,
            'auto_response_enabled' => $this->autoResponseEnabled,
            'created_at' => $this->createdAt->format('Y-m-d H:i:s'),
            'updated_at' => $this->updatedAt->format('Y-m-d H:i:s'),
        ];
    }
}
