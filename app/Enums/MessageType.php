<?php

declare(strict_types=1);

namespace App\Enums;

enum MessageType: string
{
    case TEXT = 'text';
    case IMAGE = 'image';
    case VIDEO = 'video';
    case AUDIO = 'audio';
    case FILE = 'file';
    case LOCATION = 'location';
    case CONTACT = 'contact';
    case STICKER = 'sticker';
    case TEMPLATE = 'template';
    case SYSTEM = 'system';

    public function label(): string
    {
        return match($this) {
            self::TEXT => 'Texto',
            self::IMAGE => 'Imagem',
            self::VIDEO => 'Vídeo',
            self::AUDIO => 'Áudio',
            self::FILE => 'Arquivo',
            self::LOCATION => 'Localização',
            self::CONTACT => 'Contato',
            self::STICKER => 'Figurinha',
            self::TEMPLATE => 'Template',
            self::SYSTEM => 'Sistema',
        };
    }

    public function isMedia(): bool
    {
        return in_array($this, [
            self::IMAGE,
            self::VIDEO,
            self::AUDIO,
            self::FILE,
            self::STICKER,
        ]);
    }

    public static function getValues(): array
    {
        return array_column(self::cases(), 'value');
    }
}
