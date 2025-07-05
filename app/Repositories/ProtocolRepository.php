<?php

namespace App\Repositories;

use App\DTOs\CreateProtocolDTO;
use App\DTOs\UpdateProtocolDTO;
use App\DTOs\ProtocolDTO;
use App\Models\Protocol;
use Illuminate\Database\Eloquent\Collection;

/**
 * Protocol Repository
 * 
 * Responsável pelo acesso aos dados de protocolos.
 * Segue padrões SOLID: SRP - única responsabilidade de acesso a dados.
 */
class ProtocolRepository
{
    /**
     * Cria um novo protocolo
     */
    public function create(CreateProtocolDTO $dto, string $protocolNumber): ProtocolDTO
    {
        $data = $dto->toArray();
        $data['protocol_number'] = $protocolNumber;

        // Se está sendo atribuído, define assigned_at
        if ($dto->assignedToUserId) {
            $data['assigned_at'] = now();
        }

        $protocol = Protocol::create($data);

        return ProtocolDTO::fromModel($protocol);
    }

    /**
     * Atualiza um protocolo existente
     */
    public function update(int $protocolId, UpdateProtocolDTO $dto): ?ProtocolDTO
    {
        $protocol = Protocol::find($protocolId);

        if (!$protocol) {
            return null;
        }

        $data = $dto->toArray();

        // Lógica de timestamps automáticos
        if (isset($data['status'])) {
            $this->handleStatusTimestamps($protocol, $data['status'], $data);
        }

        if (isset($data['assigned_to_user_id']) && !$protocol->assigned_at) {
            $data['assigned_at'] = now();
        }

        $protocol->update($data);

        return ProtocolDTO::fromModel($protocol->fresh());
    }

    /**
     * Busca protocolo por ID
     */
    public function findById(int $id, array $relations = []): ?ProtocolDTO
    {
        $query = Protocol::query();

        if (!empty($relations)) {
            $query->with($relations);
        }

        $protocol = $query->find($id);

        return $protocol ? ProtocolDTO::fromModel($protocol) : null;
    }

    /**
     * Busca protocolo por número
     */
    public function findByNumber(string $protocolNumber, array $relations = []): ?ProtocolDTO
    {
        $query = Protocol::query();

        if (!empty($relations)) {
            $query->with($relations);
        }

        $protocol = $query->where('protocol_number', $protocolNumber)->first();

        return $protocol ? ProtocolDTO::fromModel($protocol) : null;
    }

    /**
     * Lista protocolos de um contato
     */
    public function findByContact(int $contactId, array $relations = []): array
    {
        $query = Protocol::forContact($contactId)
            ->orderBy('created_at', 'desc');

        if (!empty($relations)) {
            $query->with($relations);
        }

        return $query->get()
            ->map(fn($protocol) => ProtocolDTO::fromModel($protocol))
            ->toArray();
    }

    /**
     * Lista protocolos atribuídos a um usuário
     */
    public function findByAssignedUser(int $userId, array $relations = []): array
    {
        $query = Protocol::assignedTo($userId)
            ->orderBy('priority', 'desc')
            ->orderBy('created_at', 'asc');

        if (!empty($relations)) {
            $query->with($relations);
        }

        return $query->get()
            ->map(fn($protocol) => ProtocolDTO::fromModel($protocol))
            ->toArray();
    }

    /**
     * Lista protocolos por status
     */
    public function findByStatus(string $status, array $relations = []): array
    {
        $query = Protocol::where('status', $status)
            ->orderBy('priority', 'desc')
            ->orderBy('created_at', 'asc');

        if (!empty($relations)) {
            $query->with($relations);
        }

        return $query->get()
            ->map(fn($protocol) => ProtocolDTO::fromModel($protocol))
            ->toArray();
    }

    /**
     * Lista protocolos urgentes
     */
    public function findUrgent(array $relations = []): array
    {
        $query = Protocol::urgent()
            ->whereNotIn('status', ['closed', 'cancelled'])
            ->orderBy('created_at', 'asc');

        if (!empty($relations)) {
            $query->with($relations);
        }

        return $query->get()
            ->map(fn($protocol) => ProtocolDTO::fromModel($protocol))
            ->toArray();
    }

    /**
     * Lista protocolos criados em um período
     */
    public function findByDateRange(\DateTime $startDate, \DateTime $endDate, array $relations = []): array
    {
        $query = Protocol::createdBetween($startDate, $endDate)
            ->orderBy('created_at', 'desc');

        if (!empty($relations)) {
            $query->with($relations);
        }

        return $query->get()
            ->map(fn($protocol) => ProtocolDTO::fromModel($protocol))
            ->toArray();
    }

    /**
     * Conta protocolos por status
     */
    public function countByStatus(): array
    {
        return Protocol::selectRaw('status, COUNT(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();
    }

    /**
     * Conta protocolos por prioridade
     */
    public function countByPriority(): array
    {
        return Protocol::selectRaw('priority, COUNT(*) as count')
            ->groupBy('priority')
            ->pluck('count', 'priority')
            ->toArray();
    }

    /**
     * Obtém último número de protocolo do dia
     */
    public function getLastProtocolNumberForToday(): ?string
    {
        $prefix = date('Ymd');

        return Protocol::where('protocol_number', 'like', $prefix . '%')
            ->orderBy('id', 'desc')
            ->value('protocol_number');
    }

    /**
     * Verifica se número de protocolo já existe
     */
    public function protocolNumberExists(string $protocolNumber): bool
    {
        return Protocol::where('protocol_number', $protocolNumber)->exists();
    }

    /**
     * Deleta protocolo (soft delete)
     */
    public function delete(int $protocolId): bool
    {
        $protocol = Protocol::find($protocolId);

        if (!$protocol) {
            return false;
        }

        return $protocol->delete();
    }

    /**
     * Lista protocolos com paginação e filtros
     */
    public function paginate(array $filters = [], int $perPage = 15): \Illuminate\Contracts\Pagination\LengthAwarePaginator
    {
        $query = Protocol::query()->with(['contact', 'conversation', 'createdByUser', 'assignedToUser']);

        // Aplica filtros
        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (isset($filters['contact_id'])) {
            $query->where('contact_id', $filters['contact_id']);
        }

        if (isset($filters['protocol_number'])) {
            $query->where('protocol_number', 'like', '%' . $filters['protocol_number'] . '%');
        }

        if (isset($filters['priority'])) {
            $query->where('priority', $filters['priority']);
        }

        if (isset($filters['assigned_to_user_id'])) {
            $query->where('assigned_to_user_id', $filters['assigned_to_user_id']);
        }

        if (isset($filters['created_by_user_id'])) {
            $query->where('created_by_user_id', $filters['created_by_user_id']);
        }

        if (isset($filters['date_from'])) {
            $query->whereDate('created_at', '>=', $filters['date_from']);
        }

        if (isset($filters['date_to'])) {
            $query->whereDate('created_at', '<=', $filters['date_to']);
        }

        // Ordenação padrão: mais recentes primeiro
        $query->orderBy('created_at', 'desc');

        return $query->paginate($perPage);
    }

    /**
     * Gerencia timestamps automáticos baseado em mudanças de status
     */
    private function handleStatusTimestamps(Protocol $protocol, string $newStatus, array &$data): void
    {
        $oldStatus = $protocol->status;

        // Se está fechando o protocolo
        if (in_array($newStatus, ['closed', 'cancelled']) && !in_array($oldStatus, ['closed', 'cancelled'])) {
            $data['closed_at'] = now();
        }

        // Se está resolvendo o protocolo
        if ($newStatus === 'resolved' && $oldStatus !== 'resolved') {
            $data['resolved_at'] = now();
        }

        // Se está reabrindo um protocolo fechado
        if (!in_array($newStatus, ['closed', 'cancelled']) && in_array($oldStatus, ['closed', 'cancelled'])) {
            $data['closed_at'] = null;
        }

        // Se está voltando de resolvido
        if ($newStatus !== 'resolved' && $oldStatus === 'resolved') {
            $data['resolved_at'] = null;
        }
    }
}
