<?php

declare(strict_types=1);

namespace App\Repositories\Eloquent;

use App\DTOs\ContactDTO;
use App\DTOs\CreateContactDTO;
use App\Models\Contact;
use App\Repositories\Contracts\ContactRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

class EloquentContactRepository implements ContactRepositoryInterface
{
    /**
     * Campos pesquisáveis para filtros
     */
    private array $searchableFields = [
        'name',
        'email',
        'phone',
        'document',
        'is_blocked'
    ];

    public function __construct(
        private Contact $model
    ) {}

    public function find(int $id): ?ContactDTO
    {
        $contact = $this->model->find($id);

        return $contact ? ContactDTO::fromModel($contact) : null;
    }

    public function create(CreateContactDTO $dto): ContactDTO
    {
        $contact = $this->model->create($dto->toArray());

        return ContactDTO::fromModel($contact);
    }

    public function update(int $id, array $data): ContactDTO
    {
        /** @var Contact $contact */
        $contact = $this->model->findOrFail($id);
        $contact->update($data);
        $contact->refresh();

        return ContactDTO::fromModel($contact);
    }

    public function delete(int $id): bool
    {
        return $this->model->destroy($id) > 0;
    }

    public function findByPhone(string $phone): ?ContactDTO
    {
        $contact = $this->model->where('phone', $phone)->first();

        return $contact ? ContactDTO::fromModel($contact) : null;
    }

    public function findByEmail(string $email): ?ContactDTO
    {
        $contact = $this->model->where('email', $email)->first();

        return $contact ? ContactDTO::fromModel($contact) : null;
    }

    public function findByDocument(string $document): ?ContactDTO
    {
        $contact = $this->model->where('document', $document)->first();

        return $contact ? ContactDTO::fromModel($contact) : null;
    }

    public function search(string $query): array
    {
        $contacts = $this->model
            ->where('name', 'like', "%{$query}%")
            ->orWhere('phone', 'like', "%{$query}%")
            ->orWhere('email', 'like', "%{$query}%")
            ->limit(50)
            ->get();

        return $contacts->map(fn($contact) => ContactDTO::fromModel($contact))->toArray();
    }

    public function paginate(int $perPage = 15, array $filters = []): LengthAwarePaginator
    {
        $query = $this->applyFilters($this->model->newQuery(), $filters);

        return $query->paginate($perPage);
    }

    public function getBlocked(): array
    {
        $contacts = $this->model->where('is_blocked', true)->get();

        return $contacts->map(fn($contact) => ContactDTO::fromModel($contact))->toArray();
    }

    public function block(int $contactId, int $blockedBy, string $reason): ContactDTO
    {
        /** @var Contact $contact */
        $contact = $this->model->findOrFail($contactId);

        $contact->update([
            'is_blocked' => true,
            'blocked_by' => $blockedBy,
            'blocked_reason' => $reason,
            'blocked_at' => now(),
        ]);
        $contact->refresh();

        return ContactDTO::fromModel($contact);
    }

    public function unblock(int $contactId): ContactDTO
    {
        /** @var Contact $contact */
        $contact = $this->model->findOrFail($contactId);

        $contact->update([
            'is_blocked' => false,
            'blocked_by' => null,
            'blocked_reason' => null,
            'blocked_at' => null,
        ]);
        $contact->refresh();

        return ContactDTO::fromModel($contact);
    }

    public function advancedSearch(\Illuminate\Http\Request $request, array $relations = []): mixed
    {
        $query = $this->model->newQuery();

        // Aplicar filtros básicos
        $filters = $request->only($this->searchableFields);
        $query = $this->applyFilters($query, $filters);

        // Carregar relacionamentos
        if (!empty($relations)) {
            $query->with($relations);
        }

        // Busca por texto se fornecido
        if ($search = $request->get('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        return $query->paginate($request->get('per_page', 15));
    }

    public function searchWithFilters(array $filters, array $relations = []): mixed
    {
        $query = $this->model->newQuery();

        $query = $this->applyFilters($query, $filters);

        if (!empty($relations)) {
            $query->with($relations);
        }

        return $query->get();
    }

    public function autocompleteSearch(\Illuminate\Http\Request $request, array $select = [], array $conditions = []): mixed
    {
        $query = $this->model->newQuery();

        if (!empty($select)) {
            $query->select($select);
        }

        if ($search = $request->get('q')) {
            $query->where(function ($q) use ($search, $conditions) {
                foreach ($conditions as $field) {
                    $q->orWhere($field, 'like', "%{$search}%");
                }
            });
        }

        return $query->limit(20)->get();
    }

    public function batchUpdate(array $ids, array $data): bool
    {
        $affected = $this->model->whereIn('id', $ids)->update($data);
        return $affected > 0;
    }

    public function findByIn(string $column, array $values, array $with = []): mixed
    {
        $query = $this->model->whereIn($column, $values);

        if (!empty($with)) {
            $query->with($with);
        }

        return $query->get();
    }

    /**
     * Aplica filtros na query
     */
    private function applyFilters(Builder $query, array $filters): Builder
    {
        foreach ($filters as $field => $value) {
            if (in_array($field, $this->searchableFields) && $value !== null) {
                if (is_array($value)) {
                    $query->whereIn($field, $value);
                } else {
                    $query->where($field, $value);
                }
            }
        }

        return $query;
    }
}
