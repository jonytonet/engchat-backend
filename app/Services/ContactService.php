<?php

declare(strict_types=1);

namespace App\Services;

use App\DTOs\ContactDTO;
use App\DTOs\CreateContactDTO;
use App\Models\Contact;
use App\Repositories\Contracts\ContactRepositoryInterface;
use Illuminate\Http\Request;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ContactService
{
    protected ContactRepositoryInterface $contactRepository;

    public function __construct(
        ContactRepositoryInterface $contactRepository
    ) {
        $this->contactRepository = $contactRepository;
    }

    /**
     * Busca contatos com filtros avançados
     */
    public function searchContacts(Request $request): mixed
    {
        return $this->contactRepository->advancedSearch($request);
    }

    /**
     * Busca com filtros específicos
     */
    public function searchWithFilters(array $filters, array $relations = []): mixed
    {
        return $this->contactRepository->searchWithFilters($filters, $relations);
    }

    /**
     * Autocomplete para contatos
     */
    public function autocompleteContacts(Request $request): mixed
    {
        $select = ['id', 'name', 'email', 'phone'];
        $conditions = ['name', 'email'];
        return $this->contactRepository->autocompleteSearch($request, $select, $conditions);
    }

    /**
     * Atualização em lote
     */
    public function batchUpdate(array $ids, array $data): bool
    {
        return $this->contactRepository->batchUpdate($ids, $data);
    }

    /**
     * Cria um novo contato
     */
    public function create(CreateContactDTO $dto): ContactDTO
    {
        // Validações de negócio específicas
        $this->validateBusinessRules($dto);

        // Verificar se já existe contato com mesmo telefone ou email
        if ($dto->phone && $this->contactRepository->findByPhone($dto->phone)) {
            throw new \Exception('Já existe um contato com este telefone.');
        }

        if ($dto->email && $this->contactRepository->findByEmail($dto->email)) {
            throw new \Exception('Já existe um contato com este email.');
        }

        return $this->contactRepository->create($dto);
    }

    /**
     * Busca contato por ID (usando DTO)
     */
    public function findById(int $id): ?ContactDTO
    {
        return $this->contactRepository->find($id);
    }

    /**
     * Atualiza um contato
     */
    public function update(int $id, array $data): ContactDTO
    {
        return $this->contactRepository->update($id, $data);
    }

    /**
     * Remove um contato
     */
    public function delete(int $id): bool
    {
        return $this->contactRepository->delete($id);
    }

    /**
     * Lista contatos com paginação e filtros
     */
    public function getAll(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return $this->contactRepository->searchWithFilters($filters);
    }

    /**
     * Busca contatos por telefone
     */
    public function findByPhone(string $phone): ?ContactDTO
    {
        return $this->contactRepository->findByPhone($phone);
    }

    /**
     * Busca contatos por email
     */
    public function findByEmail(string $email): ?ContactDTO
    {
        return $this->contactRepository->findByEmail($email);
    }

    /**
     * Busca para autocomplete
     */
    public function autocomplete(Request $request): array
    {
        $result = $this->contactRepository->autocompleteSearch(
            $request,
            ['id', 'name', 'phone', 'email'],
            ['name', 'phone', 'email']
        );
        return $result->toArray();
    }

    /**
     * Busca avançada
     */
    public function advancedSearch(Request $request): mixed
    {
        return $this->contactRepository->advancedSearch($request);
    }

    /**
     * Bloqueia um contato
     */
    public function block(int $contactId, int $blockedBy, string $reason): ContactDTO
    {
        return $this->contactRepository->block($contactId, $blockedBy, $reason);
    }

    /**
     * Desbloqueia um contato
     */
    public function unblock(int $contactId): ContactDTO
    {
        return $this->contactRepository->unblock($contactId);
    }

    /**
     * Lista contatos bloqueados
     */
    public function getBlocked(): array
    {
        return $this->contactRepository->getBlocked();
    }

    /**
     * Estatísticas de contatos
     */
    public function getStatistics(): array
    {
        // Implementar através de queries no repositório
        return [
            'total' => Contact::count(),
            'blocked' => Contact::where('is_blocked', true)->count(),
            'active' => Contact::where('is_blocked', false)->count(),
            'today' => Contact::whereDate('created_at', today())->count(),
        ];
    }

    /**
     * Operações em lote
     */
    public function bulkBlock(array $contactIds, int $blockedBy, string $reason): bool
    {
        return $this->contactRepository->batchUpdate($contactIds, [
            'is_blocked' => true,
            'blocked_by' => $blockedBy,
            'blocked_reason' => $reason,
            'blocked_at' => now()->toDateTimeString(),
        ]);
    }

    /**
     * Desbloqueia em lote
     */
    public function bulkUnblock(array $contactIds): bool
    {
        return $this->contactRepository->batchUpdate($contactIds, [
            'is_blocked' => false,
            'blocked_by' => null,
            'blocked_reason' => null,
            'blocked_at' => null,
        ]);
    }

    /**
     * Busca contatos por IDs
     */
    public function findByIds(array $ids): array
    {
        // Usar método delegado através do __call
        $contacts = $this->contactRepository->findByIn('id', $ids);
        return $contacts->map(fn($contact) => ContactDTO::fromModel($contact))->toArray();
    }

    /**
     * Validações de negócio
     */
    private function validateBusinessRules(CreateContactDTO $dto): void
    {
        // Implementar validações específicas de negócio
        if (empty($dto->name) && empty($dto->phone) && empty($dto->email)) {
            throw new \Exception('Contato deve ter pelo menos nome, telefone ou email.');
        }
    }
}
