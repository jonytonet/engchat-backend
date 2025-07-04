<?php

namespace Database\Seeders;

use App\Models\Department;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Buscar roles e departments
        $adminRole = Role::where('name', 'admin')->first();
        $managerRole = Role::where('name', 'manager')->first();
        $agentRole = Role::where('name', 'agent')->first();

        $supportDepartment = Department::where('name', 'Suporte Técnico')->first();
        $salesDepartment = Department::where('name', 'Vendas')->first();
        $customerServiceDepartment = Department::where('name', 'Atendimento ao Cliente')->first();

        // Criar usuário administrador principal
        $admin = User::updateOrCreate(
            ['email' => 'admin@engchat.com'],
            [
                'name' => 'Administrador EngChat',
                'email' => 'admin@engchat.com',
                'password' => Hash::make('admin123'),
                'avatar' => null,
                'status' => 'online',
                'role_id' => $adminRole?->id,
                'department_id' => null, // Admin não pertence a departamento específico
                'manager_id' => null,
                'last_activity' => now(),
                'timezone' => 'America/Sao_Paulo',
                'language' => 'pt-BR',
                'is_active' => true,
                'email_verified_at' => now(),
            ]
        );

        // Criar gerente de suporte
        $supportManager = User::updateOrCreate(
            ['email' => 'gerente.suporte@engchat.com'],
            [
                'name' => 'João Silva',
                'email' => 'gerente.suporte@engchat.com',
                'password' => Hash::make('manager123'),
                'avatar' => null,
                'status' => 'online',
                'role_id' => $managerRole?->id,
                'department_id' => $supportDepartment?->id,
                'manager_id' => $admin->id,
                'last_activity' => now(),
                'timezone' => 'America/Sao_Paulo',
                'language' => 'pt-BR',
                'is_active' => true,
                'email_verified_at' => now(),
            ]
        );

        // Atualizar department com o gerente
        if ($supportDepartment && $supportManager) {
            $supportDepartment->update(['manager_id' => $supportManager->id]);
        }

        // Criar gerente de vendas
        $salesManager = User::updateOrCreate(
            ['email' => 'gerente.vendas@engchat.com'],
            [
                'name' => 'Maria Santos',
                'email' => 'gerente.vendas@engchat.com',
                'password' => Hash::make('manager123'),
                'avatar' => null,
                'status' => 'online',
                'role_id' => $managerRole?->id,
                'department_id' => $salesDepartment?->id,
                'manager_id' => $admin->id,
                'last_activity' => now(),
                'timezone' => 'America/Sao_Paulo',
                'language' => 'pt-BR',
                'is_active' => true,
                'email_verified_at' => now(),
            ]
        );

        // Atualizar department com o gerente
        if ($salesDepartment && $salesManager) {
            $salesDepartment->update(['manager_id' => $salesManager->id]);
        }

        // Criar alguns agentes de exemplo
        $agents = [
            [
                'name' => 'Carlos Oliveira',
                'email' => 'carlos.suporte@engchat.com',
                'department_id' => $supportDepartment?->id,
                'manager_id' => $supportManager?->id,
            ],
            [
                'name' => 'Ana Costa',
                'email' => 'ana.suporte@engchat.com',
                'department_id' => $supportDepartment?->id,
                'manager_id' => $supportManager?->id,
            ],
            [
                'name' => 'Pedro Almeida',
                'email' => 'pedro.vendas@engchat.com',
                'department_id' => $salesDepartment?->id,
                'manager_id' => $salesManager?->id,
            ],
            [
                'name' => 'Julia Ferreira',
                'email' => 'julia.vendas@engchat.com',
                'department_id' => $salesDepartment?->id,
                'manager_id' => $salesManager?->id,
            ],
            [
                'name' => 'Roberto Lima',
                'email' => 'roberto.atendimento@engchat.com',
                'department_id' => $customerServiceDepartment?->id,
                'manager_id' => null,
            ],
        ];

        foreach ($agents as $agentData) {
            User::updateOrCreate(
                ['email' => $agentData['email']],
                array_merge($agentData, [
                    'password' => Hash::make('agent123'),
                    'avatar' => null,
                    'status' => 'offline',
                    'role_id' => $agentRole?->id,
                    'last_activity' => now()->subHours(rand(1, 8)),
                    'timezone' => 'America/Sao_Paulo',
                    'language' => 'pt-BR',
                    'is_active' => true,
                    'email_verified_at' => now(),
                ])
            );
        }

        $this->command->info('Usuários criados com sucesso!');
        $this->command->info('Credenciais do Admin:');
        $this->command->info('Email: admin@engchat.com');
        $this->command->info('Senha: admin123');
        $this->command->info('');
        $this->command->info('Credenciais dos Gerentes:');
        $this->command->info('Email: gerente.suporte@engchat.com | Senha: manager123');
        $this->command->info('Email: gerente.vendas@engchat.com | Senha: manager123');
        $this->command->info('');
        $this->command->info('Credenciais dos Agentes: agent123 (para todos)');
    }
}
