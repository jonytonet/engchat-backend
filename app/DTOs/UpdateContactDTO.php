<?php

declare(strict_types=1);

namespace App\DTOs;

use App\Enums\Priority;

readonly class UpdateContactDTO
{
    public function __construct(
        public ?string $name = null,
        public ?string $email = null,
        public ?string $phone = null,
        public ?string $displayName = null,
        public ?string $company = null,
        public ?string $document = null,
        public ?array $tags = null,
        public ?Priority $priority = null,
        public ?bool $blacklisted = null,
        public ?string $blacklistReason = null,
        public ?string $preferredLanguage = null,
        public ?string $timezone = null
    ) {}

    public function toArray(): array
    {
        $data = [];

        if ($this->name !== null) $data['name'] = $this->name;
        if ($this->email !== null) $data['email'] = $this->email;
        if ($this->phone !== null) $data['phone'] = $this->phone;
        if ($this->displayName !== null) $data['display_name'] = $this->displayName;
        if ($this->company !== null) $data['company'] = $this->company;
        if ($this->document !== null) $data['document'] = $this->document;
        if ($this->tags !== null) $data['tags'] = $this->tags;
        if ($this->priority !== null) $data['priority'] = $this->priority->value;
        if ($this->blacklisted !== null) $data['blacklisted'] = $this->blacklisted;
        if ($this->blacklistReason !== null) $data['blacklist_reason'] = $this->blacklistReason;
        if ($this->preferredLanguage !== null) $data['preferred_language'] = $this->preferredLanguage;
        if ($this->timezone !== null) $data['timezone'] = $this->timezone;

        return $data;
    }

    public static function fromRequest(\Illuminate\Http\Request $request): self
    {
        return new self(
            name: $request->input('name'),
            email: $request->input('email'),
            phone: $request->input('phone'),
            displayName: $request->input('display_name'),
            company: $request->input('company'),
            document: $request->input('document'),
            tags: $request->input('tags'),
            priority: $request->input('priority') ? Priority::from($request->input('priority')) : null,
            blacklisted: $request->input('blacklisted'),
            blacklistReason: $request->input('blacklist_reason'),
            preferredLanguage: $request->input('preferred_language'),
            timezone: $request->input('timezone')
        );
    }
}
