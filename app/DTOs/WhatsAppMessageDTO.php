<?php

namespace App\DTOs;

/**
 * DTO para mensagens do WhatsApp
 * 
 * Segue padrões SOLID/DDD: dados imutáveis, validação na construção.
 */
readonly class WhatsAppMessageDTO
{
    public function __construct(
        public string $to,
        public string $type,
        public string $content,
        public ?string $templateName = null,
        public ?array $templateComponents = null,
        public ?string $mediaUrl = null,
        public ?string $caption = null,
        public ?string $filename = null
    ) {
        $this->validate();
    }

    public static function createTextMessage(string $to, string $message): self
    {
        return new self(
            to: $to,
            type: 'text',
            content: $message
        );
    }

    public static function createTemplateMessage(
        string $to, 
        string $templateName, 
        array $components = []
    ): self {
        return new self(
            to: $to,
            type: 'template',
            content: '',
            templateName: $templateName,
            templateComponents: $components
        );
    }

    public static function createMediaMessage(
        string $to,
        string $type,
        string $mediaUrl,
        ?string $caption = null,
        ?string $filename = null
    ): self {
        return new self(
            to: $to,
            type: $type,
            content: '',
            mediaUrl: $mediaUrl,
            caption: $caption,
            filename: $filename
        );
    }

    private function validate(): void
    {
        if (empty($this->to)) {
            throw new \InvalidArgumentException('Número de destino é obrigatório');
        }

        if (!in_array($this->type, ['text', 'template', 'image', 'video', 'audio', 'document'])) {
            throw new \InvalidArgumentException('Tipo de mensagem inválido');
        }

        if ($this->type === 'text' && empty($this->content)) {
            throw new \InvalidArgumentException('Conteúdo da mensagem de texto é obrigatório');
        }

        if ($this->type === 'template' && empty($this->templateName)) {
            throw new \InvalidArgumentException('Nome do template é obrigatório');
        }

        if (in_array($this->type, ['image', 'video', 'audio', 'document']) && empty($this->mediaUrl)) {
            throw new \InvalidArgumentException('URL da mídia é obrigatória');
        }
    }

    public function toArray(): array
    {
        return [
            'to' => $this->to,
            'type' => $this->type,
            'content' => $this->content,
            'template_name' => $this->templateName,
            'template_components' => $this->templateComponents,
            'media_url' => $this->mediaUrl,
            'caption' => $this->caption,
            'filename' => $this->filename
        ];
    }
}
