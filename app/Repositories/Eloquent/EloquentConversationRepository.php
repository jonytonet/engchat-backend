<?php

declare(strict_types=1);

namespace App\Repositories\Eloquent;

use App\DTOs\ConversationDTO;
use App\DTOs\CreateConversationDTO;
use App\Enums\ConversationStatus;
use App\Enums\Priority;
use App\Models\Conversation;
use App\Repositories\Contracts\ConversationRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class EloquentConversationRepository implements ConversationRepositoryInterface
{
    /**
     * Campos pesquisáveis para filtros
     */
    private array $searchableFields = [
        'status',
        'priority',
        'subject',
        'contact_id',
        'channel_id',
        'category_id',
        'assigned_to'
    ];

    public function __construct(
        private Conversation $model
    ) {}

    public function find(int $id): ?ConversationDTO
    {
        $conversation = $this->model->find($id);

        return $conversation ? ConversationDTO::fromModel($conversation) : null;
    }

    public function create(CreateConversationDTO $dto): ConversationDTO
    {
        $conversation = $this->model->create($dto->toArray());

        return ConversationDTO::fromModel($conversation);
    }

    public function update(int $id, array $data): ConversationDTO
    {
        /** @var Conversation $conversation */
        $conversation = $this->model->findOrFail($id);
        $conversation->update($data);
        $conversation->refresh();

        return ConversationDTO::fromModel($conversation);
    }

    public function delete(int $id): bool
    {
        return $this->model->destroy($id) > 0;
    }

    public function findActiveByContact(int $contactId): ?ConversationDTO
    {
        $conversation = $this->model
            ->where('contact_id', $contactId)
            ->whereIn('status', [ConversationStatus::OPEN, ConversationStatus::ASSIGNED])
            ->first();

        return $conversation ? ConversationDTO::fromModel($conversation) : null;
    }

    public function findByChannel(int $channelId): array
    {
        $conversations = $this->model
            ->where('channel_id', $channelId)
            ->get();

        return $conversations->map(fn($conv) => ConversationDTO::fromModel($conv))->toArray();
    }

    public function findByStatus(ConversationStatus $status): array
    {
        $conversations = $this->model
            ->where('status', $status)
            ->get();

        return $conversations->map(fn($conv) => ConversationDTO::fromModel($conv))->toArray();
    }

    public function findByPriority(Priority $priority): array
    {
        $conversations = $this->model
            ->where('priority', $priority)
            ->get();

        return $conversations->map(fn($conv) => ConversationDTO::fromModel($conv))->toArray();
    }

    public function findByAgent(int $agentId): array
    {
        $conversations = $this->model
            ->where('assigned_to', $agentId)
            ->get();

        return $conversations->map(fn($conv) => ConversationDTO::fromModel($conv))->toArray();
    }

    public function assignToAgent(int $conversationId, int $agentId): ConversationDTO
    {
        /** @var Conversation $conversation */
        $conversation = $this->model->findOrFail($conversationId);

        $conversation->update([
            'assigned_to' => $agentId,
            'status' => ConversationStatus::ASSIGNED,
        ]);
        $conversation->refresh();

        return ConversationDTO::fromModel($conversation);
    }

    public function close(int $conversationId, int $closedBy): ConversationDTO
    {
        /** @var Conversation $conversation */
        $conversation = $this->model->findOrFail($conversationId);

        $conversation->update([
            'status' => ConversationStatus::CLOSED,
            'closed_by' => $closedBy,
            'closed_at' => now(),
        ]);
        $conversation->refresh();

        return ConversationDTO::fromModel($conversation);
    }

    public function reopen(int $conversationId): ConversationDTO
    {
        /** @var Conversation $conversation */
        $conversation = $this->model->findOrFail($conversationId);

        $conversation->update([
            'status' => ConversationStatus::OPEN,
            'closed_by' => null,
            'closed_at' => null,
        ]);
        $conversation->refresh();

        return ConversationDTO::fromModel($conversation);
    }

    public function updateLastMessage(int $conversationId): void
    {
        /** @var Conversation $conversation */
        $conversation = $this->model->findOrFail($conversationId);

        $conversation->update([
            'last_message_at' => now(),
        ]);
    }

    public function paginate(int $perPage = 15, array $filters = []): LengthAwarePaginator
    {
        $query = $this->applyFilters($this->model->newQuery(), $filters);

        return $query->paginate($perPage);
    }

    public function getStatistics(): array
    {
        return [
            'total' => $this->model->count(),
            'open' => $this->model->where('status', ConversationStatus::OPEN)->count(),
            'assigned' => $this->model->where('status', ConversationStatus::ASSIGNED)->count(),
            'closed' => $this->model->where('status', ConversationStatus::CLOSED)->count(),
            'today' => $this->model->whereDate('created_at', today())->count(),
        ];
    }

    public function findOldConversations(int $daysOld = 7): array
    {
        $conversations = $this->model
            ->where('last_message_at', '<', now()->subDays($daysOld))
            ->whereNotIn('status', [ConversationStatus::CLOSED, ConversationStatus::ARCHIVED])
            ->get();

        return $conversations->map(fn($conv) => ConversationDTO::fromModel($conv))->toArray();
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
                $q->where('subject', 'like', "%{$search}%")
                    ->orWhereHas('contact', function ($contact) use ($search) {
                        $contact->where('name', 'like', "%{$search}%")
                            ->orWhere('phone', 'like', "%{$search}%");
                    });
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

    public function getStatsByDateRange(string $startDate, string $endDate): array
    {
        $conversations = $this->model
            ->whereBetween('created_at', [$startDate, $endDate])
            ->get();

        return [
            'total' => $conversations->count(),
            'by_status' => $conversations->groupBy('status')->map->count(),
            'by_priority' => $conversations->groupBy('priority')->map->count(),
            'avg_response_time' => $conversations->avg('first_response_time'),
        ];
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
