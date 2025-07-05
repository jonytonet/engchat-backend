<?php

namespace App\DTOs;

/**
 * DTO para resposta da API do WhatsApp
 * 
 * Padroniza as respostas da API para uso interno.
 */
readonly class WhatsAppResponseDTO
{
    public function __construct(
        public bool $success,
        public ?string $messageId = null,
        public ?array $data = null,
        public ?string $error = null,
        public ?string $errorCode = null,
        public int $statusCode = 200
    ) {}

    public static function success(array $data, ?string $messageId = null): self
    {
        return new self(
            success: true,
            messageId: $messageId,
            data: $data,
            statusCode: 200
        );
    }

    public static function error(string $error, ?string $errorCode = null, int $statusCode = 400): self
    {
        return new self(
            success: false,
            error: $error,
            errorCode: $errorCode,
            statusCode: $statusCode
        );
    }

    public function toArray(): array
    {
        return [
            'success' => $this->success,
            'message_id' => $this->messageId,
            'data' => $this->data,
            'error' => $this->error,
            'error_code' => $this->errorCode,
            'status_code' => $this->statusCode
        ];
    }
}
