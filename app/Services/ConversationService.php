<?php

declare(strict_types=1);

namespace App\Services;

use App\DTOs\ConversationDTO;
use App\DTOs\CreateConversationDTO;
use App\Enums\ConversationStatus;
use App\Events\ConversationCreated;
use App\Events\ConversationAssigned;
use App\Events\ConversationClosed;
use App\Events\ConversationReopened;
use App\Repositories\Contracts\ConversationRepositoryInterface;
use App\Repositories\Contracts\ContactRepositoryInterface;
use Illuminate\Contracts\Events\Dispatcher as EventDispatcher;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class ConversationService
{
    protected ConversationRepositoryInterface $conversationRepository;
    protected ContactRepositoryInterface $contactRepository;
    protected EventDispatcher $eventDispatcher;

    public function __construct(
        ConversationRepositoryInterface $conversationRepository,
        ContactRepositoryInterface $contactRepository,
        EventDispatcher $eventDispatcher
    ) {
        $this->conversationRepository = $conversationRepository;
        $this->contactRepository = $contactRepository;
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * Busca conversas com filtros avançados
     */
    public function searchConversations(Request $request): mixed
    {
        $relations = ['contact', 'category', 'channel'];
        return $this->conversationRepository->advancedSearch($request, $relations);
    }

    /**
     * Busca com filtros específicos
     */
    public function searchWithFilters(array $filters, array $relations = []): mixed
    {
        return $this->conversationRepository->searchWithFilters($filters, $relations);
    }

    /**
     * Autocomplete para conversas
     */
    public function autocompleteConversations(Request $request): mixed
    {
        $select = ['id', 'subject', 'status'];
        $conditions = ['subject'];
        return $this->conversationRepository->autocompleteSearch($request, $select, $conditions);
    }

    /**
     * Atualização em lote
     */
    public function batchUpdate(array $ids, array $data): bool
    {
        return $this->conversationRepository->batchUpdate($ids, $data);
    }

    /**
     * Estatísticas por período
     */
    public function getStatsByDateRange(string $startDate, string $endDate): array
    {
        return $this->conversationRepository->getStatsByDateRange($startDate, $endDate);
    }

    /**
     * Busca uma conversa por ID
     */
    public function findById(int $id): ?ConversationDTO
    {
        return $this->conversationRepository->find($id);
    }

    public function create(CreateConversationDTO $dto): ConversationDTO
    {
        // Validações de negócio
        $this->validateBusinessRules($dto);

        // Verificar se já existe conversa ativa para este contato
        $existingConversation = $this->conversationRepository->findActiveByContact($dto->contactId);

        if ($existingConversation) {
            throw ValidationException::withMessages([
                'contact_id' => 'Já existe uma conversa ativa para este contato.'
            ]);
        }

        // Criar a conversa
        $conversation = $this->conversationRepository->create($dto);

        // Disparar evento
        $this->eventDispatcher->dispatch(new ConversationCreated($conversation));

        return $conversation;
    }

    public function assignToAgent(int $conversationId, int $agentId, int $assignedBy = null): ConversationDTO
    {
        // Validar se a conversa pode ser atribuída
        $conversation = $this->conversationRepository->find($conversationId);

        if (!$conversation) {
            throw new \InvalidArgumentException('Conversa não encontrada.');
        }

        if ($conversation->status === ConversationStatus::CLOSED->value) {
            throw ValidationException::withMessages([
                'conversation' => 'Não é possível atribuir uma conversa fechada.'
            ]);
        }

        // Atribuir ao agente
        $updatedConversation = $this->conversationRepository->assignToAgent($conversationId, $agentId);

        // Disparar evento
        $this->eventDispatcher->dispatch(new ConversationAssigned($updatedConversation, $agentId));

        return $updatedConversation;
    }

    public function close(int $conversationId, int $closedBy): ConversationDTO
    {
        // Validar se a conversa pode ser fechada
        $conversation = $this->conversationRepository->find($conversationId);

        if (!$conversation) {
            throw new \InvalidArgumentException('Conversa não encontrada.');
        }

        if ($conversation->status === ConversationStatus::CLOSED->value) {
            throw ValidationException::withMessages([
                'conversation' => 'Esta conversa já está fechada.'
            ]);
        }

        // Fechar a conversa
        $closedConversation = $this->conversationRepository->close($conversationId, $closedBy);

        // Disparar evento
        $this->eventDispatcher->dispatch(new ConversationClosed($closedConversation));

        return $closedConversation;
    }

    /**
     * Busca conversas do usuário/agente autenticado
     */
    public function getUserConversations(int $userId, array $filters = [], int $perPage = 15): \Illuminate\Contracts\Pagination\LengthAwarePaginator
    {
        $filters['assigned_to'] = $userId;
        return $this->conversationRepository->paginate($perPage, $filters);
    }

    /**
     * Lista todas as conversas com filtros (para admin)
     */
    public function getAll(array $filters = [], int $perPage = 25): \Illuminate\Contracts\Pagination\LengthAwarePaginator
    {
        return $this->conversationRepository->paginate($perPage, $filters);
    }

    /**
     * Atualiza uma conversa
     */
    public function update(int $id, array $data): ConversationDTO
    {
        $conversation = $this->conversationRepository->update($id, $data);

        // Disparar eventos se necessário
        if (isset($data['status'])) {
            $this->handleStatusChange($conversation, $data['status']);
        }

        return $conversation;
    }

    /**
     * Remove uma conversa (soft delete)
     */
    public function delete(int $id): bool
    {
        return $this->conversationRepository->delete($id);
    }

    /**
     * Busca conversas por contato
     */
    public function findByContact(int $contactId): array
    {
        return $this->conversationRepository->findByChannel($contactId);
    }

    /**
     * Busca mensagens de uma conversa
     */
    public function getMessages(int $conversationId): array
    {
        // TODO: Implementar MessageRepository
        return [];
    }

    /**
     * Estatísticas do agente
     */
    public function getAgentStatistics(int $agentId): array
    {
        return [
            'total_conversations' => count($this->conversationRepository->findByAgent($agentId)),
            'open_conversations' => count($this->conversationRepository->findByStatus(ConversationStatus::OPEN)),
            'assigned_conversations' => count($this->conversationRepository->findByStatus(ConversationStatus::ASSIGNED)),
            'closed_today' => 0, // TODO: Implementar com filtro de data
        ];
    }

    /**
     * Estatísticas do dashboard administrativo
     */
    public function getDashboardStatistics(): array
    {
        return $this->conversationRepository->getStatistics();
    }

    /**
     * Atribuição em lote
     */
    public function bulkAssign(array $conversationIds, int $agentId, int $assignedBy): void
    {
        foreach ($conversationIds as $conversationId) {
            $this->assignToAgent($conversationId, $agentId);
        }
    }

    /**
     * Fechamento em lote
     */
    public function bulkClose(array $conversationIds, int $closedBy): void
    {
        foreach ($conversationIds as $conversationId) {
            $this->close($conversationId, $closedBy);
        }
    }

    /**
     * Exportação de conversas
     */
    public function exportConversations(array $filters): \Symfony\Component\HttpFoundation\BinaryFileResponse
    {
        // TODO: Implementar exportação (Excel/CSV)
        throw new \Exception('Exportação ainda não implementada');
    }

    /**
     * Reabre uma conversa
     */
    public function reopen(int $conversationId, int $reopenedBy = null): ConversationDTO
    {
        $conversation = $this->conversationRepository->reopen($conversationId);

        $this->eventDispatcher->dispatch(new ConversationReopened($conversation));

        return $conversation;
    }

    /**
     * Trata mudanças de status
     */
    private function handleStatusChange(ConversationDTO $conversation, string $newStatus): void
    {
        $status = ConversationStatus::from($newStatus);

        match($status) {
            ConversationStatus::ASSIGNED => $this->eventDispatcher->dispatch(
                new ConversationAssigned($conversation, $conversation->assignedTo ?? 0)
            ),
            ConversationStatus::CLOSED => $this->eventDispatcher->dispatch(new ConversationClosed($conversation)),
            default => null
        };
    }

    private function validateBusinessRules(CreateConversationDTO $dto): void
    {
        // Verificar se o contato existe e não está bloqueado
        $contact = $this->contactRepository->find($dto->contactId);

        if (!$contact) {
            throw ValidationException::withMessages([
                'contact_id' => 'Contato não encontrado.'
            ]);
        }

        if ($contact->isBlocked) {
            throw ValidationException::withMessages([
                'contact_id' => 'Não é possível criar conversa com contato bloqueado.'
            ]);
        }
    }
}
