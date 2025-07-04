<?php

declare(strict_types=1);

namespace App\DTOs;

use App\Enums\Priority;

readonly class CreateContactDTO
{
    public function __construct(
        public string $name,
        public ?string $email = null,
        public ?string $phone = null,
        public ?string $displayName = null,
        public ?string $company = null,
        public ?string $document = null,
        public array $tags = [],
        public Priority $priority = Priority::MEDIUM,
        public string $preferredLanguage = 'pt-BR',
        public string $timezone = 'America/Sao_Paulo'
    ) {}

    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
            'display_name' => $this->displayName,
            'company' => $this->company,
            'document' => $this->document,
            'tags' => $this->tags,
            'priority' => $this->priority->value,
            'blacklisted' => false,
            'preferred_language' => $this->preferredLanguage,
            'timezone' => $this->timezone,
            'total_interactions' => 0,
        ];
    }

    public static function fromRequest(\App\Http\Requests\CreateContactRequest $request): self
    {
        return new self(
            name: $request->validated('name'),
            email: $request->validated('email'),
            phone: $request->validated('phone'),
            displayName: $request->validated('display_name'),
            company: $request->validated('company'),
            document: $request->validated('document'),
            tags: $request->validated('tags', []),
            priority: Priority::from($request->validated('priority', 'medium')),
            preferredLanguage: $request->validated('preferred_language', 'pt-BR'),
            timezone: $request->validated('timezone', 'America/Sao_Paulo')
        );
    }
}
