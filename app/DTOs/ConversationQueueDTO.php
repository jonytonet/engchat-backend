<?php

declare(strict_types=1);

namespace App\DTOs;

use DateTime;

/**
 * DTO para Conversation Queue
 * 
 * @property-read int $conversation_id
 * @property-read int $department_id
 * @property-read int|null $category_id
 * @property-read string $priority
 * @property-read int $queue_position
 * @property-read int|null $estimated_wait_time
 * @property-read string $status
 * @property-read int|null $assigned_to
 */
readonly class ConversationQueueDTO
{
    public function __construct(
        public int $conversation_id,
        public int $department_id,
        public ?int $category_id,
        public string $priority,
        public int $queue_position,
        public ?int $estimated_wait_time = null,
        public int $assignment_attempts = 0,
        public int $notification_count = 0,
        public string $status = 'waiting',
        public ?int $assigned_to = null,
        public ?DateTime $entered_queue_at = null,
        public ?DateTime $assigned_at = null,
    ) {}

    /**
     * Cria um DTO a partir de um array
     */
    public static function fromArray(array $data): self
    {
        return new self(
            conversation_id: $data['conversation_id'],
            department_id: $data['department_id'],
            category_id: $data['category_id'] ?? null,
            priority: $data['priority'] ?? 'medium',
            queue_position: $data['queue_position'],
            estimated_wait_time: $data['estimated_wait_time'] ?? null,
            assignment_attempts: $data['assignment_attempts'] ?? 0,
            notification_count: $data['notification_count'] ?? 0,
            status: $data['status'] ?? 'waiting',
            assigned_to: $data['assigned_to'] ?? null,
            entered_queue_at: isset($data['entered_queue_at']) ? new DateTime($data['entered_queue_at']) : null,
            assigned_at: isset($data['assigned_at']) ? new DateTime($data['assigned_at']) : null,
        );
    }

    /**
     * Converte o DTO para array
     */
    public function toArray(): array
    {
        return [
            'conversation_id' => $this->conversation_id,
            'department_id' => $this->department_id,
            'category_id' => $this->category_id,
            'priority' => $this->priority,
            'queue_position' => $this->queue_position,
            'estimated_wait_time' => $this->estimated_wait_time,
            'assignment_attempts' => $this->assignment_attempts,
            'notification_count' => $this->notification_count,
            'status' => $this->status,
            'assigned_to' => $this->assigned_to,
            'entered_queue_at' => $this->entered_queue_at?->format('Y-m-d H:i:s'),
            'assigned_at' => $this->assigned_at?->format('Y-m-d H:i:s'),
        ];
    }

    /**
     * Verifica se está aguardando na fila
     */
    public function isWaiting(): bool
    {
        return $this->status === 'waiting';
    }

    /**
     * Verifica se foi atribuído a um agente
     */
    public function isAssigned(): bool
    {
        return $this->status === 'assigned' && $this->assigned_to !== null;
    }

    /**
     * Verifica se expirou
     */
    public function isExpired(): bool
    {
        return $this->status === 'expired';
    }

    /**
     * Verifica se foi cancelado
     */
    public function isCancelled(): bool
    {
        return $this->status === 'cancelled';
    }

    /**
     * Obtém o tempo de espera em minutos
     */
    public function getWaitTimeMinutes(): int
    {
        if (!$this->entered_queue_at) {
            return 0;
        }

        $now = new DateTime();
        $diff = $now->diff($this->entered_queue_at);

        return ($diff->h * 60) + $diff->i;
    }

    /**
     * Verifica se deve enviar notificação de posição
     */
    public function shouldSendPositionNotification(int $maxNotifications, int $intervalMinutes): bool
    {
        if ($this->notification_count >= $maxNotifications) {
            return false;
        }

        $waitTime = $this->getWaitTimeMinutes();
        $nextNotificationTime = ($this->notification_count + 1) * $intervalMinutes;

        return $waitTime >= $nextNotificationTime;
    }

    /**
     * Obtém prioridade numérica para ordenação
     */
    public function getPriorityWeight(): int
    {
        return match ($this->priority) {
            'urgent' => 1,
            'high' => 2,
            'medium' => 3,
            'low' => 4,
            default => 3
        };
    }

    /**
     * Atualiza a posição na fila
     */
    public function withPosition(int $newPosition): self
    {
        return new self(
            conversation_id: $this->conversation_id,
            department_id: $this->department_id,
            category_id: $this->category_id,
            priority: $this->priority,
            queue_position: $newPosition,
            estimated_wait_time: $this->estimated_wait_time,
            assignment_attempts: $this->assignment_attempts,
            notification_count: $this->notification_count,
            status: $this->status,
            assigned_to: $this->assigned_to,
            entered_queue_at: $this->entered_queue_at,
            assigned_at: $this->assigned_at,
        );
    }

    /**
     * Atribui a um agente
     */
    public function assignTo(int $agentId): self
    {
        return new self(
            conversation_id: $this->conversation_id,
            department_id: $this->department_id,
            category_id: $this->category_id,
            priority: $this->priority,
            queue_position: $this->queue_position,
            estimated_wait_time: $this->estimated_wait_time,
            assignment_attempts: $this->assignment_attempts,
            notification_count: $this->notification_count,
            status: 'assigned',
            assigned_to: $agentId,
            entered_queue_at: $this->entered_queue_at,
            assigned_at: new DateTime(),
        );
    }

    /**
     * Incrementa tentativas de atribuição
     */
    public function incrementAssignmentAttempts(): self
    {
        return new self(
            conversation_id: $this->conversation_id,
            department_id: $this->department_id,
            category_id: $this->category_id,
            priority: $this->priority,
            queue_position: $this->queue_position,
            estimated_wait_time: $this->estimated_wait_time,
            assignment_attempts: $this->assignment_attempts + 1,
            notification_count: $this->notification_count,
            status: $this->status,
            assigned_to: $this->assigned_to,
            entered_queue_at: $this->entered_queue_at,
            assigned_at: $this->assigned_at,
        );
    }

    /**
     * Incrementa contador de notificações
     */
    public function incrementNotificationCount(): self
    {
        return new self(
            conversation_id: $this->conversation_id,
            department_id: $this->department_id,
            category_id: $this->category_id,
            priority: $this->priority,
            queue_position: $this->queue_position,
            estimated_wait_time: $this->estimated_wait_time,
            assignment_attempts: $this->assignment_attempts,
            notification_count: $this->notification_count + 1,
            status: $this->status,
            assigned_to: $this->assigned_to,
            entered_queue_at: $this->entered_queue_at,
            assigned_at: $this->assigned_at,
        );
    }
}
