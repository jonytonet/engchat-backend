<?php

declare(strict_types=1);

namespace App\DTOs;

use App\Enums\ConversationStatus;
use App\Enums\Priority;

readonly class UpdateConversationDTO
{
    public function __construct(
        public ?int $categoryId = null,
        public ?int $assignedTo = null,
        public ?ConversationStatus $status = null,
        public ?Priority $priority = null,
        public ?int $satisfactionRating = null,
        public ?array $tags = null,
        public ?\DateTime $closedAt = null
    ) {}

    public function toArray(): array
    {
        $data = [];

        if ($this->categoryId !== null) $data['category_id'] = $this->categoryId;
        if ($this->assignedTo !== null) $data['assigned_to'] = $this->assignedTo;
        if ($this->status !== null) $data['status'] = $this->status->value;
        if ($this->priority !== null) $data['priority'] = $this->priority->value;
        if ($this->satisfactionRating !== null) $data['satisfaction_rating'] = $this->satisfactionRating;
        if ($this->tags !== null) $data['tags'] = $this->tags;
        if ($this->closedAt !== null) $data['closed_at'] = $this->closedAt;

        return $data;
    }

    public static function fromRequest(\Illuminate\Http\Request $request): self
    {
        return new self(
            categoryId: $request->input('category_id'),
            assignedTo: $request->input('assigned_to'),
            status: $request->input('status') ? ConversationStatus::from($request->input('status')) : null,
            priority: $request->input('priority') ? Priority::from($request->input('priority')) : null,
            satisfactionRating: $request->input('satisfaction_rating'),
            tags: $request->input('tags'),
            closedAt: $request->input('closed_at') ? new \DateTime($request->input('closed_at')) : null
        );
    }
}
