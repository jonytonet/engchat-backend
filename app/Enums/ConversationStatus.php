<?php

declare(strict_types=1);

namespace App\Enums;

enum ConversationStatus: string
{
    case OPEN = 'open';
    case ASSIGNED = 'assigned';
    case CLOSED = 'closed';
    case ARCHIVED = 'archived';

    public function label(): string
    {
        return match($this) {
            self::OPEN => 'Aberta',
            self::ASSIGNED => 'Atribuída',
            self::CLOSED => 'Fechada',
            self::ARCHIVED => 'Arquivada',
        };
    }

    public function color(): string
    {
        return match($this) {
            self::OPEN => 'yellow',
            self::ASSIGNED => 'blue',
            self::CLOSED => 'green',
            self::ARCHIVED => 'gray',
        };
    }

    public static function getValues(): array
    {
        return array_column(self::cases(), 'value');
    }
}
