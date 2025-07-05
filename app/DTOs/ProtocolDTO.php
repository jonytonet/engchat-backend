<?php

namespace App\DTOs;

use App\Models\Protocol;
use Carbon\Carbon;

/**
 * Protocol DTO
 * 
 * Transfere dados de protocolo entre camadas seguindo padrões SOLID/DDD.
 */
readonly class ProtocolDTO
{
    public function __construct(
        public int $id,
        public string $protocolNumber,
        public int $contactId,
        public ?int $conversationId,
        public ?int $createdByUserId,
        public ?int $assignedToUserId,
        public string $status,
        public string $priority,
        public ?string $subject,
        public ?string $description,
        public ?string $resolutionNotes,
        public ?array $metadata,
        public Carbon $openedAt,
        public ?Carbon $assignedAt,
        public ?Carbon $resolvedAt,
        public ?Carbon $closedAt,
        public Carbon $createdAt,
        public Carbon $updatedAt,
        // Campos relacionais opcionais
        public ?ContactDTO $contact = null,
        public ?ConversationDTO $conversation = null,
        public ?UserDTO $createdByUser = null,
        public ?UserDTO $assignedToUser = null
    ) {}

    public static function fromModel(Protocol $protocol): self
    {
        return new self(
            id: $protocol->id,
            protocolNumber: $protocol->protocol_number,
            contactId: $protocol->contact_id,
            conversationId: $protocol->conversation_id,
            createdByUserId: $protocol->created_by_user_id,
            assignedToUserId: $protocol->assigned_to_user_id,
            status: $protocol->status,
            priority: $protocol->priority,
            subject: $protocol->subject,
            description: $protocol->description,
            resolutionNotes: $protocol->resolution_notes,
            metadata: $protocol->metadata,
            openedAt: $protocol->opened_at,
            assignedAt: $protocol->assigned_at,
            resolvedAt: $protocol->resolved_at,
            closedAt: $protocol->closed_at,
            createdAt: $protocol->created_at,
            updatedAt: $protocol->updated_at,
            contact: $protocol->relationLoaded('contact') && $protocol->contact ?
                ContactDTO::fromModel($protocol->contact) : null,
            conversation: $protocol->relationLoaded('conversation') && $protocol->conversation ?
                ConversationDTO::fromModel($protocol->conversation) : null,
            createdByUser: $protocol->relationLoaded('createdByUser') && $protocol->createdByUser ?
                UserDTO::fromModel($protocol->createdByUser) : null,
            assignedToUser: $protocol->relationLoaded('assignedToUser') && $protocol->assignedToUser ?
                UserDTO::fromModel($protocol->assignedToUser) : null
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'protocol_number' => $this->protocolNumber,
            'contact_id' => $this->contactId,
            'conversation_id' => $this->conversationId,
            'created_by_user_id' => $this->createdByUserId,
            'assigned_to_user_id' => $this->assignedToUserId,
            'status' => $this->status,
            'priority' => $this->priority,
            'subject' => $this->subject,
            'description' => $this->description,
            'resolution_notes' => $this->resolutionNotes,
            'metadata' => $this->metadata,
            'opened_at' => $this->openedAt->toISOString(),
            'assigned_at' => $this->assignedAt?->toISOString(),
            'resolved_at' => $this->resolvedAt?->toISOString(),
            'closed_at' => $this->closedAt?->toISOString(),
            'created_at' => $this->createdAt->toISOString(),
            'updated_at' => $this->updatedAt->toISOString(),
            'contact' => $this->contact?->toArray(),
            'conversation' => $this->conversation?->toArray(),
            'created_by_user' => $this->createdByUser?->toArray(),
            'assigned_to_user' => $this->assignedToUser?->toArray()
        ];
    }

    // Métodos de conveniência
    public function isOpen(): bool
    {
        return $this->status === 'open';
    }

    public function isClosed(): bool
    {
        return in_array($this->status, ['closed', 'cancelled']);
    }

    public function isInProgress(): bool
    {
        return $this->status === 'in_progress';
    }

    public function isResolved(): bool
    {
        return $this->status === 'resolved';
    }

    public function isUrgent(): bool
    {
        return $this->priority === 'urgent';
    }

    public function isAssigned(): bool
    {
        return !is_null($this->assignedToUserId);
    }

    public function getLifetimeInHours(): float
    {
        $endTime = $this->closedAt ?? now();
        return (float) $this->openedAt->diffInHours($endTime);
    }

    public function getLifetimeInDays(): float
    {
        $endTime = $this->closedAt ?? now();
        return (float) $this->openedAt->diffInDays($endTime, true);
    }

    public function getFormattedStatus(): string
    {
        return match ($this->status) {
            'open' => 'Aberto',
            'in_progress' => 'Em Andamento',
            'resolved' => 'Resolvido',
            'closed' => 'Fechado',
            'cancelled' => 'Cancelado',
            default => 'Status Desconhecido'
        };
    }

    public function getFormattedPriority(): string
    {
        return match ($this->priority) {
            'low' => 'Baixa',
            'normal' => 'Normal',
            'high' => 'Alta',
            'urgent' => 'Urgente',
            default => 'Prioridade Desconhecida'
        };
    }
}
