<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\DTOs\ConversationDTO;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ConversationResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param Request $request
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        /** @var ConversationDTO $conversation */
        $conversation = $this->resource;

        return [
            'id' => $conversation->id,
            'contact_id' => $conversation->contactId,
            'channel_id' => $conversation->channelId,
            'category_id' => $conversation->categoryId,
            'assigned_to' => $conversation->assignedTo,
            'status' => $conversation->status,
            'priority' => $conversation->priority,
            'subject' => $conversation->subject,
            'last_message_at' => $conversation->lastMessageAt?->toISOString(),
            'closed_at' => $conversation->closedAt?->toISOString(),
            'closed_by' => $conversation->closedBy,
            'metadata' => $conversation->metadata,
            'created_at' => $conversation->createdAt->toISOString(),
            'updated_at' => $conversation->updatedAt->toISOString(),

            // Relacionamentos carregados sob demanda
            'contact' => $this->whenLoaded('contact', function () {
                return ContactResource::make($this->contact);
            }),

            'channel' => $this->whenLoaded('channel', function () {
                return ChannelResource::make($this->channel);
            }),

            'category' => $this->whenLoaded('category', function () {
                return CategoryResource::make($this->category);
            }),

            'assigned_agent' => $this->whenLoaded('assignedAgent', function () {
                return UserResource::make($this->assignedAgent);
            }),

            'latest_message' => $this->whenLoaded('latestMessage', function () {
                return MessageResource::make($this->latestMessage);
            }),

            'messages_count' => $this->when(
                isset($this->messages_count),
                $this->messages_count
            ),
        ];
    }

    /**
     * Get additional data that should be returned with the resource array.
     *
     * @param Request $request
     * @return array<string, mixed>
     */
    public function with(Request $request): array
    {
        return [
            'meta' => [
                'version' => '1.0',
                'timestamp' => now()->toISOString(),
            ],
        ];
    }
}
