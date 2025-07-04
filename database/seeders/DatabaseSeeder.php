<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->command->info('🚀 Iniciando seed do EngChat...');

        // Executar seeders na ordem correta (dependências)
        $this->call([
            RoleSeeder::class,          // 1. Roles primeiro
            DepartmentSeeder::class,    // 2. Departments (sem manager ainda)
            CategorySeeder::class,      // 3. Categories
            ChannelSeeder::class,       // 4. Channels
            AdminUserSeeder::class,     // 5. Users (incluindo managers para departments)
        ]);

        $this->command->info('✅ Seed do EngChat concluído com sucesso!');
        $this->command->info('');
        $this->command->info('🔑 Credenciais de acesso:');
        $this->command->info('📧 Admin: admin@engchat.com | 🔒 Senha: admin123');
        $this->command->info('📧 Gerente Suporte: gerente.suporte@engchat.com | 🔒 Senha: manager123');
        $this->command->info('📧 Gerente Vendas: gerente.vendas@engchat.com | 🔒 Senha: manager123');
        $this->command->info('');
        $this->command->info('🌐 Acesse: http://localhost:8000/login');
    }
}
