<?php

declare(strict_types=1);

namespace App\Events;

use App\DTOs\ConversationDTO;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ConversationClosed implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public readonly ConversationDTO $conversation
    ) {}

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('conversations'),
            new PrivateChannel("conversations.{$this->conversation->id}"),
        ];
    }

    public function broadcastAs(): string
    {
        return 'conversation.closed';
    }

    public function broadcastWith(): array
    {
        return [
            'conversation' => $this->conversation->toArray(),
        ];
    }
}
