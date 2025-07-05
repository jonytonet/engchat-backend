<?php

namespace App\Services;

use App\DTOs\CreateProtocolDTO;
use App\DTOs\UpdateProtocolDTO;
use App\DTOs\ProtocolDTO;
use App\Repositories\ProtocolRepository;
use App\Services\ContactQueryService;
use Illuminate\Support\Facades\Log;

/**
 * Protocol Service
 * 
 * Implementa a lógica de negócio para protocolos.
 * Segue padrões SOLID/DDD:
 * - SRP: Responsabilidade única de gerenciar protocolos
 * - DIP: Depende de abstrações (Repository)
 * - OCP: Extensível para novas regras de negócio
 */
class ProtocolService
{
    public function __construct(
        private ProtocolRepository $protocolRepository,
        private ContactQueryService $contactQueryService
    ) {}

    /**
     * Cria um novo protocolo
     */
    public function createProtocol(CreateProtocolDTO $dto): ProtocolDTO
    {
        // Validação de negócio: contato deve existir
        // Usando findByPhone temporariamente, pode ser adaptado conforme necessário
        $contact = \App\Models\Contact::find($dto->contactId);
        if (!$contact) {
            throw new \InvalidArgumentException('Contato não encontrado');
        }

        // Gera número único do protocolo
        $protocolNumber = $this->generateUniqueProtocolNumber();

        // Cria o protocolo
        $protocol = $this->protocolRepository->create($dto, $protocolNumber);

        Log::info('Protocolo criado com sucesso', [
            'protocol_id' => $protocol->id,
            'protocol_number' => $protocol->protocolNumber,
            'contact_id' => $protocol->contactId,
            'created_by_user_id' => $protocol->createdByUserId
        ]);

        return $protocol;
    }

    /**
     * Atualiza um protocolo existente
     */
    public function updateProtocol(int $protocolId, array $data): ?ProtocolDTO
    {
        // Busca o protocolo atual
        $currentProtocol = $this->protocolRepository->findById($protocolId);
        if (!$currentProtocol) {
            return null;
        }

        // Cria DTO a partir dos dados
        $dto = new UpdateProtocolDTO(
            status: $data['status'] ?? null,
            priority: $data['priority'] ?? null,
            description: $data['description'] ?? null,
            resolutionNotes: $data['notes'] ?? null,
            assignedToUserId: $data['assigned_to_user_id'] ?? null
        );

        // Validações de negócio
        if ($dto->status) {
            $this->validateStatusTransition($currentProtocol->status, $dto->status);
        }

        // Atualiza o protocolo
        $updatedProtocol = $this->protocolRepository->update($protocolId, $dto);
        if (!$updatedProtocol) {
            throw new \RuntimeException('Falha ao atualizar protocolo');
        }

        Log::info('Protocolo atualizado com sucesso', [
            'protocol_id' => $updatedProtocol->id,
            'protocol_number' => $updatedProtocol->protocolNumber,
            'old_status' => $currentProtocol->status,
            'new_status' => $updatedProtocol->status
        ]);

        return $updatedProtocol;
    }

    /**
     * Atualiza um protocolo existente (sobrecarga do método original)
     */
    public function updateProtocolWithDTO(int $protocolId, UpdateProtocolDTO $dto): ProtocolDTO
    {
        // Busca o protocolo atual
        $currentProtocol = $this->protocolRepository->findById($protocolId);
        if (!$currentProtocol) {
            throw new \InvalidArgumentException('Protocolo não encontrado');
        }

        // Validações de negócio
        $this->validateStatusTransition($currentProtocol->status, $dto->status);

        // Atualiza o protocolo
        $updatedProtocol = $this->protocolRepository->update($protocolId, $dto);
        if (!$updatedProtocol) {
            throw new \RuntimeException('Falha ao atualizar protocolo');
        }

        Log::info('Protocolo atualizado com sucesso', [
            'protocol_id' => $updatedProtocol->id,
            'protocol_number' => $updatedProtocol->protocolNumber,
            'old_status' => $currentProtocol->status,
            'new_status' => $updatedProtocol->status
        ]);

        return $updatedProtocol;
    }

    /**
     * Atribui protocolo a um usuário
     */
    public function assignProtocol(int $protocolId, int $userId): ProtocolDTO
    {
        $dto = new UpdateProtocolDTO(assignedToUserId: $userId);
        return $this->updateProtocolWithDTO($protocolId, $dto);
    }

    /**
     * Fecha um protocolo
     */
    public function closeProtocol(int $protocolId, ?string $resolutionNotes = null): ?ProtocolDTO
    {
        $dto = new UpdateProtocolDTO(
            status: 'closed',
            resolutionNotes: $resolutionNotes
        );

        return $this->updateProtocol($protocolId, $dto->toArray());
    }

    /**
     * Resolve um protocolo
     */
    public function resolveProtocol(int $protocolId, string $resolutionNotes): ProtocolDTO
    {
        $dto = new UpdateProtocolDTO(
            status: 'resolved',
            resolutionNotes: $resolutionNotes
        );

        return $this->updateProtocolWithDTO($protocolId, $dto);
    }

    /**
     * Cancela um protocolo
     */
    public function cancelProtocol(int $protocolId, string $reason): ProtocolDTO
    {
        $dto = new UpdateProtocolDTO(
            status: 'cancelled',
            resolutionNotes: "Cancelado: {$reason}"
        );

        return $this->updateProtocolWithDTO($protocolId, $dto);
    }

    /**
     * Reabre um protocolo fechado
     */
    public function reopenProtocol(int $protocolId, ?string $reason = null): ?ProtocolDTO
    {
        $currentProtocol = $this->protocolRepository->findById($protocolId);
        if (!$currentProtocol) {
            return null;
        }

        if (!$currentProtocol->isClosed()) {
            throw new \InvalidArgumentException('Apenas protocolos fechados podem ser reabertos');
        }

        $description = $currentProtocol->description;
        if ($reason) {
            $description .= "\n\nReaberto: {$reason}";
        }

        $dto = new UpdateProtocolDTO(
            status: 'open',
            description: $description
        );

        return $this->updateProtocol($protocolId, $dto->toArray());
    }

    /**
     * Busca protocolo por ID
     */
    public function findProtocol(int $protocolId, bool $includeRelations = false): ?ProtocolDTO
    {
        $relations = $includeRelations ? ['contact', 'conversation', 'createdByUser', 'assignedToUser'] : [];
        return $this->protocolRepository->findById($protocolId, $relations);
    }

    /**
     * Busca protocolo por ID (alias para compatibilidade)
     */
    public function findProtocolById(int $protocolId, bool $includeRelations = false): ?ProtocolDTO
    {
        return $this->findProtocol($protocolId, $includeRelations);
    }

    /**
     * Lista protocolos com filtros
     */
    public function listProtocols(array $filters = []): \Illuminate\Contracts\Pagination\LengthAwarePaginator
    {
        $perPage = $filters['per_page'] ?? 15;
        unset($filters['per_page']);

        return $this->protocolRepository->paginate($filters, $perPage);
    }

    /**
     * Busca protocolo por número
     */
    public function findProtocolByNumber(string $protocolNumber, bool $includeRelations = false): ?ProtocolDTO
    {
        $relations = $includeRelations ? ['contact', 'conversation', 'createdByUser', 'assignedToUser'] : [];
        return $this->protocolRepository->findByNumber($protocolNumber, $relations);
    }

    /**
     * Lista protocolos de um contato
     */
    public function getContactProtocols(int $contactId, bool $includeRelations = false): array
    {
        $relations = $includeRelations ? ['conversation', 'createdByUser', 'assignedToUser'] : [];
        return $this->protocolRepository->findByContact($contactId, $relations);
    }

    /**
     * Lista protocolos atribuídos a um usuário
     */
    public function getUserProtocols(int $userId, bool $includeRelations = false): array
    {
        $relations = $includeRelations ? ['contact', 'conversation', 'createdByUser'] : [];
        return $this->protocolRepository->findByAssignedUser($userId, $relations);
    }

    /**
     * Lista protocolos por status
     */
    public function getProtocolsByStatus(string $status, bool $includeRelations = false): array
    {
        $relations = $includeRelations ? ['contact', 'conversation', 'createdByUser', 'assignedToUser'] : [];
        return $this->protocolRepository->findByStatus($status, $relations);
    }

    /**
     * Lista protocolos urgentes
     */
    public function getUrgentProtocols(bool $includeRelations = false): array
    {
        $relations = $includeRelations ? ['contact', 'conversation', 'createdByUser', 'assignedToUser'] : [];
        return $this->protocolRepository->findUrgent($relations);
    }

    /**
     * Obtém estatísticas de protocolos
     */
    public function getProtocolStatistics(): array
    {
        return [
            'by_status' => $this->protocolRepository->countByStatus(),
            'by_priority' => $this->protocolRepository->countByPriority(),
            'urgent_count' => count($this->protocolRepository->findUrgent()),
            'unassigned_count' => count($this->getProtocolsByStatus('open'))
        ];
    }

    /**
     * Gera número único de protocolo
     */
    private function generateUniqueProtocolNumber(): string
    {
        $maxAttempts = 10;
        $attempts = 0;

        do {
            $protocolNumber = $this->generateProtocolNumber();
            $attempts++;

            if ($attempts > $maxAttempts) {
                throw new \RuntimeException('Não foi possível gerar número único de protocolo');
            }
        } while ($this->protocolRepository->protocolNumberExists($protocolNumber));

        return $protocolNumber;
    }

    /**
     * Gera número de protocolo no formato YYYYMMDDNNNNNN
     */
    private function generateProtocolNumber(): string
    {
        $prefix = date('Ymd');
        $lastProtocolNumber = $this->protocolRepository->getLastProtocolNumberForToday();

        if ($lastProtocolNumber) {
            // Extrair o número sequencial e incrementar
            $sequence = (int) substr($lastProtocolNumber, strlen($prefix));
            $sequence++;
        } else {
            $sequence = 1;
        }

        // Formatar com zeros à esquerda (6 dígitos para o sequencial)
        return $prefix . str_pad((string) $sequence, 6, '0', STR_PAD_LEFT);
    }

    /**
     * Valida transições de status
     */
    private function validateStatusTransition(?string $currentStatus, ?string $newStatus): void
    {
        if (!$newStatus || !$currentStatus) {
            return;
        }

        $allowedTransitions = [
            'open' => ['in_progress', 'resolved', 'closed', 'cancelled'],
            'in_progress' => ['open', 'resolved', 'closed', 'cancelled'],
            'resolved' => ['closed', 'open'], // Pode voltar para aberto se necessário
            'closed' => ['open'], // Pode ser reaberto
            'cancelled' => ['open'] // Pode ser reaberto
        ];

        if (
            !isset($allowedTransitions[$currentStatus]) ||
            !in_array($newStatus, $allowedTransitions[$currentStatus])
        ) {
            throw new \InvalidArgumentException(
                "Transição de status inválida: {$currentStatus} -> {$newStatus}"
            );
        }
    }
}
