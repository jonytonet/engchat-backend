<?php

declare(strict_types=1);

namespace App\Repositories\Eloquent;

use App\DTOs\ContactDTO;
use App\DTOs\CreateContactDTO;
use App\Models\Contact;
use App\Repositories\BaseRepository;
use App\Repositories\Contracts\ContactRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Container\Container as Application;

class EloquentContactRepositoryNew implements ContactRepositoryInterface
{
    /**
     * @var BaseRepository
     */
    protected $baseRepository;

    public function __construct(Application $app)
    {
        $this->baseRepository = new class($app) extends BaseRepository {
            protected $fieldSearchable = [
                'name',
                'email',
                'phone',
                'document',
                'is_blocked'
            ];

            public function getFieldsSearchable(): array
            {
                return $this->fieldSearchable;
            }

            public function model(): string
            {
                return Contact::class;
            }
        };
    }

    public function find(int $id): ?ContactDTO
    {
        $contact = $this->baseRepository->find($id);

        return $contact ? ContactDTO::fromModel($contact) : null;
    }

    public function create(CreateContactDTO $dto): ContactDTO
    {
        $contact = $this->baseRepository->create($dto->toArray());

        return ContactDTO::fromModel($contact);
    }

    public function update(int $id, array $data): ContactDTO
    {
        $contact = $this->baseRepository->update($data, $id);

        return ContactDTO::fromModel($contact->fresh());
    }

    public function delete(int $id): bool
    {
        return $this->baseRepository->delete($id);
    }

    public function findByPhone(string $phone): ?ContactDTO
    {
        $contact = $this->baseRepository->findBy('phone', $phone)->first();

        return $contact ? ContactDTO::fromModel($contact) : null;
    }

    public function findByEmail(string $email): ?ContactDTO
    {
        $contact = $this->baseRepository->findBy('email', $email)->first();

        return $contact ? ContactDTO::fromModel($contact) : null;
    }

    public function findByDocument(string $document): ?ContactDTO
    {
        $contact = $this->baseRepository->findBy('document', $document)->first();

        return $contact ? ContactDTO::fromModel($contact) : null;
    }

    public function search(string $query): array
    {
        $contacts = Contact::where('name', 'like', "%{$query}%")
            ->orWhere('phone', 'like', "%{$query}%")
            ->orWhere('email', 'like', "%{$query}%")
            ->limit(50)
            ->get();

        return $contacts->map(fn($contact) => ContactDTO::fromModel($contact))->toArray();
    }

    public function paginate(int $perPage = 15, array $filters = []): LengthAwarePaginator
    {
        return $this->baseRepository->paginateWithFilters($perPage, $filters);
    }

    public function getBlocked(): array
    {
        $contacts = Contact::blocked()->get();

        return $contacts->map(fn($contact) => ContactDTO::fromModel($contact))->toArray();
    }

    public function block(int $contactId, int $blockedBy, string $reason): ContactDTO
    {
        $contact = Contact::findOrFail($contactId);

        $contact->update([
            'is_blocked' => true,
            'blocked_by' => $blockedBy,
            'blocked_reason' => $reason,
            'blocked_at' => now(),
        ]);

        return ContactDTO::fromModel($contact->fresh());
    }

    public function unblock(int $contactId): ContactDTO
    {
        $contact = Contact::findOrFail($contactId);

        $contact->update([
            'is_blocked' => false,
            'blocked_by' => null,
            'blocked_reason' => null,
            'blocked_at' => null,
        ]);

        return ContactDTO::fromModel($contact->fresh());
    }

    /**
     * Métodos para busca avançada usando BaseRepository
     */
    public function advancedSearch(\Illuminate\Http\Request $request, array $relations = []): mixed
    {
        return $this->baseRepository->advancedSearch($request, $relations);
    }

    public function searchWithFilters(array $filters, array $relations = []): mixed
    {
        return $this->baseRepository->findAllFieldsAnd(
            new \Illuminate\Http\Request($filters),
            $relations
        );
    }

    public function autocompleteSearch(\Illuminate\Http\Request $request, array $select = [], array $conditions = []): mixed
    {
        return $this->baseRepository->autocompleteSearch($request, $select, $conditions);
    }

    public function batchUpdate(array $ids, array $data): bool
    {
        $result = $this->baseRepository->updateBatch($ids, $data);
        return $result > 0;
    }

    public function findByIn(string $column, array $values, array $with = []): mixed
    {
        return $this->baseRepository->findByIn($column, $values, $with);
    }

    /**
     * Delegar métodos do BaseRepository
     */
    public function __call($method, $arguments)
    {
        return $this->baseRepository->$method(...$arguments);
    }
}
