<?php

namespace App\DTOs;

/**
 * DTO para criação de protocolo
 * 
 * Encapsula dados necessários para criar um novo protocolo.
 */
readonly class CreateProtocolDTO
{
    public function __construct(
        public int $contactId,
        public ?int $conversationId = null,
        public ?int $createdByUserId = null,
        public ?int $assignedToUserId = null,
        public string $priority = 'normal',
        public ?string $subject = null,
        public ?string $description = null,
        public ?array $metadata = null
    ) {
        $this->validate();
    }

    public static function fromArray(array $data): self
    {
        return new self(
            contactId: (int) $data['contact_id'],
            conversationId: isset($data['conversation_id']) ? (int) $data['conversation_id'] : null,
            createdByUserId: isset($data['created_by_user_id']) ? (int) $data['created_by_user_id'] : null,
            assignedToUserId: isset($data['assigned_to_user_id']) ? (int) $data['assigned_to_user_id'] : null,
            priority: $data['priority'] ?? 'normal',
            subject: $data['subject'] ?? null,
            description: $data['description'] ?? null,
            metadata: $data['metadata'] ?? null
        );
    }

    private function validate(): void
    {
        if ($this->contactId <= 0) {
            throw new \InvalidArgumentException('ID do contato deve ser um inteiro positivo');
        }

        if (!in_array($this->priority, ['low', 'normal', 'high', 'urgent'])) {
            throw new \InvalidArgumentException('Prioridade inválida');
        }

        if ($this->conversationId !== null && $this->conversationId <= 0) {
            throw new \InvalidArgumentException('ID da conversa deve ser um inteiro positivo');
        }

        if ($this->createdByUserId !== null && $this->createdByUserId <= 0) {
            throw new \InvalidArgumentException('ID do usuário criador deve ser um inteiro positivo');
        }

        if ($this->assignedToUserId !== null && $this->assignedToUserId <= 0) {
            throw new \InvalidArgumentException('ID do usuário responsável deve ser um inteiro positivo');
        }
    }

    public function toArray(): array
    {
        return [
            'contact_id' => $this->contactId,
            'conversation_id' => $this->conversationId,
            'created_by_user_id' => $this->createdByUserId,
            'assigned_to_user_id' => $this->assignedToUserId,
            'priority' => $this->priority,
            'subject' => $this->subject,
            'description' => $this->description,
            'metadata' => $this->metadata,
            'status' => 'open',
            'opened_at' => now()
        ];
    }
}
