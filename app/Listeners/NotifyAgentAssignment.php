<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Events\ConversationAssigned;
use App\Services\ConversationService;
use Illuminate\Contracts\Queue\ShouldQueue;

class NotifyAgentAssignment implements ShouldQueue
{
    public function __construct(
        private readonly ConversationService $conversationService
    ) {}

    public function handle(ConversationAssigned $event): void
    {
        $conversation = $event->conversation;

        // Notificar agente via websocket/push notification
        // Por enquanto, apenas log
        logger()->info('Conversa atribuída ao agente', [
            'conversation_id' => $conversation->id,
            'agent_id' => $conversation->assignedTo,
            'contact_name' => $conversation->contact->name ?? 'Não informado'
        ]);

        // TODO: Implementar notificação real via WebSocket/Pusher
        // TODO: Implementar notificação push para mobile
    }

    public function failed(ConversationAssigned $event, \Throwable $exception): void
    {
        logger()->error('Falha ao notificar atribuição de conversa', [
            'conversation_id' => $event->conversation->id,
            'agent_id' => $event->conversation->assignedTo,
            'error' => $exception->getMessage()
        ]);
    }
}
