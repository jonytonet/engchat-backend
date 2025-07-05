<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Carbon\Carbon;

/**
 * Protocol Model
 * 
 * Representa um protocolo de atendimento vinculado a um contato/conversa.
 * Seguindo padrões SOLID/DDD: apenas estrutura de dados, relacionamentos e scopes.
 */
class Protocol extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'protocol_number',
        'contact_id',
        'conversation_id',
        'created_by_user_id',
        'assigned_to_user_id',
        'status',
        'priority',
        'subject',
        'description',
        'resolution_notes',
        'metadata',
        'opened_at',
        'assigned_at',
        'resolved_at',
        'closed_at'
    ];

    protected $casts = [
        'metadata' => 'array',
        'opened_at' => 'datetime',
        'assigned_at' => 'datetime',
        'resolved_at' => 'datetime',
        'closed_at' => 'datetime'
    ];

    // =====================================================
    // RELACIONAMENTOS
    // =====================================================

    /**
     * Contato vinculado ao protocolo
     */
    public function contact(): BelongsTo
    {
        return $this->belongsTo(Contact::class);
    }

    /**
     * Conversa vinculada ao protocolo
     */
    public function conversation(): BelongsTo
    {
        return $this->belongsTo(Conversation::class);
    }

    /**
     * Usuário que criou o protocolo
     */
    public function createdByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }

    /**
     * Usuário responsável pelo protocolo
     */
    public function assignedToUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to_user_id');
    }

    // =====================================================
    // SCOPES
    // =====================================================

    /**
     * Protocolos abertos
     */
    public function scopeOpen($query)
    {
        return $query->where('status', 'open');
    }

    /**
     * Protocolos em andamento
     */
    public function scopeInProgress($query)
    {
        return $query->where('status', 'in_progress');
    }

    /**
     * Protocolos fechados
     */
    public function scopeClosed($query)
    {
        return $query->where('status', 'closed');
    }

    /**
     * Protocolos por prioridade
     */
    public function scopeByPriority($query, string $priority)
    {
        return $query->where('priority', $priority);
    }

    /**
     * Protocolos atribuídos a um usuário
     */
    public function scopeAssignedTo($query, int $userId)
    {
        return $query->where('assigned_to_user_id', $userId);
    }

    /**
     * Protocolos de um contato
     */
    public function scopeForContact($query, int $contactId)
    {
        return $query->where('contact_id', $contactId);
    }

    /**
     * Protocolos urgentes
     */
    public function scopeUrgent($query)
    {
        return $query->where('priority', 'urgent');
    }

    /**
     * Protocolos criados em um período
     */
    public function scopeCreatedBetween($query, $startDate, $endDate)
    {
        return $query->whereBetween('opened_at', [$startDate, $endDate]);
    }

    // =====================================================
    // MÉTODOS UTILITÁRIOS
    // =====================================================

    /**
     * Verifica se o protocolo está aberto
     */
    public function isOpen(): bool
    {
        return $this->status === 'open';
    }

    /**
     * Verifica se o protocolo está fechado
     */
    public function isClosed(): bool
    {
        return in_array($this->status, ['closed', 'cancelled']);
    }

    /**
     * Verifica se o protocolo está em andamento
     */
    public function isInProgress(): bool
    {
        return $this->status === 'in_progress';
    }

    /**
     * Verifica se o protocolo foi resolvido
     */
    public function isResolved(): bool
    {
        return $this->status === 'resolved';
    }

    /**
     * Verifica se o protocolo é urgente
     */
    public function isUrgent(): bool
    {
        return $this->priority === 'urgent';
    }

    /**
     * Verifica se o protocolo está atribuído
     */
    public function isAssigned(): bool
    {
        return !is_null($this->assigned_to_user_id);
    }

    /**
     * Calcula o tempo de vida do protocolo
     */
    public function getLifetimeInHours(): float
    {
        $endTime = $this->closed_at ?? now();
        return (float) Carbon::parse($this->opened_at)->diffInHours($endTime);
    }

    /**
     * Calcula o tempo de vida do protocolo em dias
     */
    public function getLifetimeInDays(): float
    {
        $endTime = $this->closed_at ?? now();
        return (float) Carbon::parse($this->opened_at)->diffInDays($endTime, true);
    }

    /**
     * Obtém o status formatado para exibição
     */
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

    /**
     * Obtém a prioridade formatada para exibição
     */
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
