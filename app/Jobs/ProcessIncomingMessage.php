<?php

declare(strict_types=1);

namespace App\Jobs;

use App\DTOs\MessageDTO;
use App\Services\BotClassificationService;
use App\Services\ConversationService;
use App\Services\MessageService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProcessIncomingMessage implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        private readonly array $messageData,
        private readonly string $channelType = 'whatsapp'
    ) {}

    public function handle(
        MessageService $messageService,
        ConversationService $conversationService,
        BotClassificationService $botService
    ): void {
        // Processar mensagem recebida
        $messageDTO = MessageDTO::fromArray($this->messageData);

        // Classificar se deve ser direcionada para bot ou agente
        $shouldUseBot = $botService->shouldHandleMessage($messageDTO);

        if ($shouldUseBot) {
            // Processar com bot
            $botService->processMessage($messageDTO);
        } else {
            // Criar ou atualizar conversa existente
            $conversation = $conversationService->findOrCreateFromMessage($messageDTO);

            // Salvar mensagem
            $messageService->createFromIncoming($messageDTO, $conversation->id);
        }
    }

    public function failed(\Throwable $exception): void
    {
        // Log do erro de processamento
        logger()->error('Falha ao processar mensagem recebida', [
            'message_data' => $this->messageData,
            'error' => $exception->getMessage(),
            'trace' => $exception->getTraceAsString()
        ]);
    }
}
