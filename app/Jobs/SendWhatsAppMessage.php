<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Services\WhatsAppWebhookService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendWhatsAppMessage implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        private readonly string $to,
        private readonly string $message,
        private readonly string $type = 'text',
        private readonly array $options = []
    ) {}

    public function handle(WhatsAppWebhookService $whatsAppService): void
    {
        $whatsAppService->sendMessage(
            to: $this->to,
            message: $this->message,
            type: $this->type,
            options: $this->options
        );
    }

    public function failed(\Throwable $exception): void
    {
        logger()->error('Falha ao enviar mensagem WhatsApp', [
            'to' => $this->to,
            'message' => $this->message,
            'error' => $exception->getMessage()
        ]);
    }
}
