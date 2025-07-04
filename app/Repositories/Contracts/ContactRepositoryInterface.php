<?php

declare(strict_types=1);

namespace App\Repositories\Contracts;

use App\DTOs\ContactDTO;
use App\DTOs\CreateContactDTO;
use Illuminate\Http\Request;

interface ContactRepositoryInterface
{
    public function find(int $id): ?ContactDTO;

    public function create(CreateContactDTO $dto): ContactDTO;

    public function update(int $id, array $data): ContactDTO;

    public function delete(int $id): bool;

    public function findByPhone(string $phone): ?ContactDTO;

    public function findByEmail(string $email): ?ContactDTO;

    public function findByDocument(string $document): ?ContactDTO;

    public function search(string $query): array;

    public function getBlocked(): array;

    public function block(int $contactId, int $blockedBy, string $reason): ContactDTO;

    public function unblock(int $contactId): ContactDTO;

    /**
     * Métodos para busca avançada
     */
    public function advancedSearch(Request $request, array $relations = []): mixed;

    public function searchWithFilters(array $filters, array $relations = []): mixed;

    public function autocompleteSearch(Request $request, array $select = [], array $conditions = []): mixed;

    public function batchUpdate(array $ids, array $data): bool;

    public function findByIn(string $column, array $values, array $with = []): mixed;
}
