<?php

declare(strict_types=1);

namespace App\Repositories\Contracts;

use App\DTOs\MessageDTO;
use App\DTOs\SendMessageDTO;
use App\Enums\MessageType;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface MessageRepositoryInterface
{
    public function find(int $id): ?MessageDTO;

    public function create(SendMessageDTO $dto): MessageDTO;

    public function update(int $id, array $data): MessageDTO;

    public function delete(int $id): bool;

    public function findByConversation(int $conversationId): array;

    public function findByType(MessageType $type): array;

    public function findByContact(int $contactId): array;

    public function findByUser(int $userId): array;

    public function findUndelivered(): array;

    public function findUnread(int $conversationId): array;

    public function markAsDelivered(int $messageId): MessageDTO;

    public function markAsRead(int $messageId): MessageDTO;

    public function markConversationAsRead(int $conversationId): int;

    public function paginate(int $conversationId, int $perPage = 50): LengthAwarePaginator;

    public function getLatestByConversation(int $conversationId): ?MessageDTO;

    public function searchContent(string $query, ?int $conversationId = null): array;
}
