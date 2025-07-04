<?php

declare(strict_types=1);

namespace App\Repositories\Contracts;

use App\DTOs\ConversationDTO;
use App\DTOs\CreateConversationDTO;
use App\Enums\ConversationStatus;
use App\Enums\Priority;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;

interface ConversationRepositoryInterface
{
    public function find(int $id): ?ConversationDTO;

    public function create(CreateConversationDTO $dto): ConversationDTO;

    public function update(int $id, array $data): ConversationDTO;

    public function delete(int $id): bool;

    public function findActiveByContact(int $contactId): ?ConversationDTO;

    public function findByChannel(int $channelId): array;

    public function findByStatus(ConversationStatus $status): array;

    public function findByPriority(Priority $priority): array;

    public function findByAgent(int $agentId): array;

    public function assignToAgent(int $conversationId, int $agentId): ConversationDTO;

    public function close(int $conversationId, int $closedBy): ConversationDTO;

    public function reopen(int $conversationId): ConversationDTO;

    public function updateLastMessage(int $conversationId): void;

    public function paginate(int $perPage = 15, array $filters = []): LengthAwarePaginator;

    public function getStatistics(): array;

    public function findOldConversations(int $daysOld = 7): array;

    /**
     * Métodos para busca avançada
     */
    public function advancedSearch(Request $request, array $relations = []): mixed;

    public function searchWithFilters(array $filters, array $relations = []): mixed;

    public function autocompleteSearch(Request $request, array $select = [], array $conditions = []): mixed;

    public function batchUpdate(array $ids, array $data): bool;

    public function getStatsByDateRange(string $startDate, string $endDate): array;
}
