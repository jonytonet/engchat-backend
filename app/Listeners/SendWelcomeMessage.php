<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Events\ConversationCreated;
use App\Jobs\SendWhatsAppMessage;
use App\Services\AutoResponseService;
use Illuminate\Contracts\Queue\ShouldQueue;

class SendWelcomeMessage implements ShouldQueue
{
    public function __construct(
        private readonly AutoResponseService $autoResponseService
    ) {}

    public function handle(ConversationCreated $event): void
    {
        $conversation = $event->conversation;

        // Buscar template de boas-vindas
        $welcomeMessage = $this->autoResponseService->getWelcomeMessage(
            channelId: $conversation->channelId,
            categoryId: $conversation->categoryId
        );

        if ($welcomeMessage) {
            // Enviar mensagem de boas-vindas
            SendWhatsAppMessage::dispatch(
                to: $conversation->contact->phone ?? '',
                message: $welcomeMessage,
                type: 'text'
            );
        }
    }

    public function failed(ConversationCreated $event, \Throwable $exception): void
    {
        logger()->error('Falha ao enviar mensagem de boas-vindas', [
            'conversation_id' => $event->conversation->id,
            'error' => $exception->getMessage()
        ]);
    }
}
