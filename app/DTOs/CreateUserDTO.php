<?php

declare(strict_types=1);

namespace App\DTOs;

readonly class CreateUserDTO
{
    public function __construct(
        public string $name,
        public string $email,
        public string $password,
        public ?string $avatar = null,
        public string $status = 'offline',
        public ?int $roleId = null,
        public ?int $departmentId = null,
        public ?int $managerId = null,
        public string $timezone = 'America/Sao_Paulo',
        public string $language = 'pt-BR',
        public bool $isActive = true
    ) {}

    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'email' => $this->email,
            'password' => bcrypt($this->password),
            'avatar' => $this->avatar,
            'status' => $this->status,
            'role_id' => $this->roleId,
            'department_id' => $this->departmentId,
            'manager_id' => $this->managerId,
            'timezone' => $this->timezone,
            'language' => $this->language,
            'is_active' => $this->isActive,
        ];
    }

    public static function fromRequest(\App\Http\Requests\CreateUserRequest $request): self
    {
        return new self(
            name: $request->validated('name'),
            email: $request->validated('email'),
            password: $request->validated('password'),
            avatar: $request->validated('avatar'),
            status: $request->validated('status', 'offline'),
            roleId: $request->validated('role_id'),
            departmentId: $request->validated('department_id'),
            managerId: $request->validated('manager_id'),
            timezone: $request->validated('timezone', 'America/Sao_Paulo'),
            language: $request->validated('language', 'pt-BR'),
            isActive: $request->validated('is_active', true)
        );
    }
}
