<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ContactResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
            'document' => $this->document,
            'avatar' => $this->avatar,
            'timezone' => $this->timezone,
            'language' => $this->language,
            'is_blocked' => $this->is_blocked,
            'blocked_reason' => $this->when($this->is_blocked, $this->blocked_reason),
            'blocked_at' => $this->when($this->is_blocked, $this->blocked_at?->toISOString()),
            'blocked_by' => $this->when($this->is_blocked && $this->blockedByAgent, [
                'id' => $this->blockedByAgent->id,
                'name' => $this->blockedByAgent->name,
            ]),
            'conversations_count' => $this->when(
                $this->relationLoaded('conversations'),
                $this->conversations_count
            ),
            'last_conversation' => $this->when(
                $this->relationLoaded('conversations') && $this->conversations->isNotEmpty(),
                function () {
                    $lastConversation = $this->conversations->sortByDesc('created_at')->first();
                    return [
                        'id' => $lastConversation->id,
                        'status' => $lastConversation->status,
                        'created_at' => $lastConversation->created_at?->toISOString(),
                    ];
                }
            ),
            'metadata' => $this->metadata,
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
