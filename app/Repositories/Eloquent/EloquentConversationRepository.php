<?php

declare(strict_types=1);

namespace App\Repositories\Eloquent;

use App\DTOs\ConversationDTO;
use App\DTOs\CreateConversationDTO;
use App\Enums\ConversationStatus;
use App\Enums\Priority;
use App\Models\Conversation;
use App\Repositories\BaseRepository;
use App\Repositories\Contracts\ConversationRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Container\Container as Application;
use Illuminate\Http\Request as HttpRequest;
use Illuminate\Http\Request;

class EloquentConversationRepository implements ConversationRepositoryInterface
{
    /**
     * @var BaseRepository
     */
    protected $baseRepository;

    public function __construct(Application $app)
    {
        $this->baseRepository = new class($app) extends BaseRepository {
            protected $fieldSearchable = [
                'status',
                'priority',
                'subject',
                'contact_id',
                'channel_id',
                'category_id',
                'assigned_to'
            ];

            public function getFieldsSearchable(): array
            {
                return $this->fieldSearchable;
            }

            public function model(): string
            {
                return Conversation::class;
            }
        };
    }

    public function find(int $id): ?ConversationDTO
    {
        $conversation = $this->baseRepository->find($id);

        return $conversation ? ConversationDTO::fromModel($conversation) : null;
    }

    public function create(CreateConversationDTO $dto): ConversationDTO
    {
        $conversation = $this->baseRepository->create($dto->toArray());

        return ConversationDTO::fromModel($conversation->fresh());
    }

    public function update(int $id, array $data): ConversationDTO
    {
        $conversation = $this->baseRepository->update($data, $id);

        return ConversationDTO::fromModel($conversation->fresh());
    }

    public function delete(int $id): bool
    {
        return $this->baseRepository->delete($id);
    }

    public function findActiveByContact(int $contactId): ?ConversationDTO
    {
        $conversation = Conversation::where('contact_id', $contactId)
            ->whereIn('status', [ConversationStatus::OPEN, ConversationStatus::ASSIGNED])
            ->first();

        return $conversation ? ConversationDTO::fromModel($conversation) : null;
    }

    public function findByChannel(int $channelId): array
    {
        $conversations = Conversation::where('channel_id', $channelId)->get();

        return $conversations->map(fn($conv) => ConversationDTO::fromModel($conv))->toArray();
    }

    public function findByStatus(ConversationStatus $status): array
    {
        $conversations = Conversation::where('status', $status)->get();

        return $conversations->map(fn($conv) => ConversationDTO::fromModel($conv))->toArray();
    }

    public function findByPriority(Priority $priority): array
    {
        $conversations = Conversation::where('priority', $priority)->get();

        return $conversations->map(fn($conv) => ConversationDTO::fromModel($conv))->toArray();
    }

    public function findByAgent(int $agentId): array
    {
        $conversations = Conversation::where('assigned_to', $agentId)->get();

        return $conversations->map(fn($conv) => ConversationDTO::fromModel($conv))->toArray();
    }

    public function assignToAgent(int $conversationId, int $agentId): ConversationDTO
    {
        $conversation = Conversation::findOrFail($conversationId);

        $conversation->update([
            'assigned_to' => $agentId,
            'status' => ConversationStatus::ASSIGNED,
        ]);

        return ConversationDTO::fromModel($conversation->fresh());
    }

    public function close(int $conversationId, int $closedBy): ConversationDTO
    {
        $conversation = Conversation::findOrFail($conversationId);

        $conversation->update([
            'status' => ConversationStatus::CLOSED,
            'closed_by' => $closedBy,
            'closed_at' => now(),
        ]);

        return ConversationDTO::fromModel($conversation->fresh());
    }

    public function reopen(int $conversationId): ConversationDTO
    {
        $conversation = Conversation::findOrFail($conversationId);

        $conversation->update([
            'status' => ConversationStatus::OPEN,
            'closed_by' => null,
            'closed_at' => null,
        ]);

        return ConversationDTO::fromModel($conversation->fresh());
    }

    public function updateLastMessage(int $conversationId): void
    {
        $conversation = Conversation::findOrFail($conversationId);

        $conversation->update([
            'last_message_at' => now(),
        ]);
    }

    public function paginate(int $perPage = 15, array $filters = []): LengthAwarePaginator
    {
        return $this->baseRepository->paginateWithFilters($perPage, $filters);
    }

    public function getStatistics(): array
    {
        return [
            'total' => Conversation::count(),
            'open' => Conversation::where('status', ConversationStatus::OPEN)->count(),
            'assigned' => Conversation::where('status', ConversationStatus::ASSIGNED)->count(),
            'closed' => Conversation::where('status', ConversationStatus::CLOSED)->count(),
            'today' => Conversation::whereDate('created_at', today())->count(),
        ];
    }

    public function findOldConversations(int $daysOld = 7): array
    {
        $conversations = Conversation::where('last_message_at', '<', now()->subDays($daysOld))
            ->whereNotIn('status', [ConversationStatus::CLOSED, ConversationStatus::ARCHIVED])
            ->get();

        return $conversations->map(fn($conv) => ConversationDTO::fromModel($conv))->toArray();
    }

    /**
     * Métodos para busca avançada usando BaseRepository
     */
    public function advancedSearch(HttpRequest $request, array $relations = []): mixed
    {
        return $this->baseRepository->advancedSearch($request, $relations);
    }

    public function searchWithFilters(array $filters, array $relations = []): mixed
    {
        return $this->baseRepository->findAllFieldsAnd(
            new HttpRequest($filters),
            $relations
        );
    }

    public function autocompleteSearch(HttpRequest $request, array $select = [], array $conditions = []): mixed
    {
        return $this->baseRepository->autocompleteSearch($request, $select, $conditions);
    }

    public function batchUpdate(array $ids, array $data): bool
    {
        $result = $this->baseRepository->updateBatch($ids, $data);
        return $result > 0;
    }

    public function getStatsByDateRange(string $startDate, string $endDate): array
    {
        $conversations = $this->baseRepository
            ->allQuery()
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
     * Delegar métodos do BaseRepository
     */
    public function __call($method, $arguments)
    {
        return $this->baseRepository->$method(...$arguments);
    }
}
