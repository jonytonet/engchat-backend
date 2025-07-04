<?php

declare(strict_types=1);

namespace App\DTOs;

readonly class CreateContactDTO
{
    public function __construct(
        public string $name,
        public ?string $email = null,
        public ?string $phone = null,
        public ?string $document = null,
        public ?string $avatar = null,
        public ?string $timezone = null,
        public ?string $language = null,
        public array $metadata = []
    ) {}

    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
            'document' => $this->document,
            'avatar' => $this->avatar,
            'timezone' => $this->timezone,
            'language' => $this->language,
            'metadata' => $this->metadata,
            'is_blocked' => false,
        ];
    }

    public static function fromRequest(\App\Http\Requests\CreateContactRequest $request): self
    {
        return new self(
            name: $request->validated('name'),
            email: $request->validated('email'),
            phone: $request->validated('phone'),
            document: $request->validated('document'),
            avatar: $request->validated('avatar'),
            timezone: $request->validated('timezone', 'America/Sao_Paulo'),
            language: $request->validated('language', 'pt_BR'),
            metadata: $request->validated('metadata', [])
        );
    }
}
