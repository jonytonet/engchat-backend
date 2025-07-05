<?php

namespace App\DTOs;

/**
 * DTO para atualização de protocolo
 * 
 * Encapsula dados necessários para atualizar um protocolo existente.
 */
readonly class UpdateProtocolDTO
{
    public function __construct(
        public ?int $assignedToUserId = null,
        public ?string $status = null,
        public ?string $priority = null,
        public ?string $subject = null,
        public ?string $description = null,
        public ?string $resolutionNotes = null,
        public ?array $metadata = null
    ) {
        $this->validate();
    }

    public static function fromArray(array $data): self
    {
        return new self(
            assignedToUserId: isset($data['assigned_to_user_id']) ? (int) $data['assigned_to_user_id'] : null,
            status: $data['status'] ?? null,
            priority: $data['priority'] ?? null,
            subject: $data['subject'] ?? null,
            description: $data['description'] ?? null,
            resolutionNotes: $data['resolution_notes'] ?? null,
            metadata: $data['metadata'] ?? null
        );
    }

    private function validate(): void
    {
        if ($this->status !== null && !in_array($this->status, ['open', 'in_progress', 'resolved', 'closed', 'cancelled'])) {
            throw new \InvalidArgumentException('Status inválido');
        }

        if ($this->priority !== null && !in_array($this->priority, ['low', 'normal', 'high', 'urgent'])) {
            throw new \InvalidArgumentException('Prioridade inválida');
        }

        if ($this->assignedToUserId !== null && $this->assignedToUserId <= 0) {
            throw new \InvalidArgumentException('ID do usuário responsável deve ser um inteiro positivo');
        }
    }

    public function toArray(): array
    {
        $data = [];

        if ($this->assignedToUserId !== null) {
            $data['assigned_to_user_id'] = $this->assignedToUserId;
        }

        if ($this->status !== null) {
            $data['status'] = $this->status;
        }

        if ($this->priority !== null) {
            $data['priority'] = $this->priority;
        }

        if ($this->subject !== null) {
            $data['subject'] = $this->subject;
        }

        if ($this->description !== null) {
            $data['description'] = $this->description;
        }

        if ($this->resolutionNotes !== null) {
            $data['resolution_notes'] = $this->resolutionNotes;
        }

        if ($this->metadata !== null) {
            $data['metadata'] = $this->metadata;
        }

        return $data;
    }

    public function hasChanges(): bool
    {
        return !empty($this->toArray());
    }
}
