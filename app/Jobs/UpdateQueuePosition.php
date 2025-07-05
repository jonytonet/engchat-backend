<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Services\ConversationService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class UpdateQueuePosition implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        private readonly int $conversationId,
        private readonly string $action = 'update'
    ) {}

    public function handle(ConversationService $conversationService): void
    {
        // Atualizar posição na fila
        match ($this->action) {
            'update' => $conversationService->updateQueuePosition($this->conversationId),
            'remove' => $conversationService->removeFromQueue($this->conversationId),
            'priority' => $conversationService->prioritizeInQueue($this->conversationId),
            default => throw new \InvalidArgumentException("Ação '{$this->action}' não reconhecida")
        };
    }

    public function failed(\Throwable $exception): void
    {
        logger()->error('Falha ao atualizar posição na fila', [
            'conversation_id' => $this->conversationId,
            'action' => $this->action,
            'error' => $exception->getMessage()
        ]);
    }
}
