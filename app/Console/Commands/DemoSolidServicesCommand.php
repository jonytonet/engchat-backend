<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Services\ContactBusinessService;
use App\Services\ContactQueryService;
use App\Services\ContactStatsService;
use App\Services\UserQueryService;
use Illuminate\Console\Command;

/**
 * Demonstração do uso dos services que seguem padrões SOLID.
 * 
 * Este comando mostra como utilizar os services especializados
 * em vez de métodos nos models que violavam o SRP.
 */
class DemoSolidServicesCommand extends Command
{
    protected $signature = 'demo:solid-services';
    protected $description = 'Demonstra o uso dos services que seguem padrões SOLID';

    public function __construct(
        private readonly UserQueryService $userQueryService,
        private readonly ContactQueryService $contactQueryService,
        private readonly ContactStatsService $contactStatsService,
        private readonly ContactBusinessService $contactBusinessService
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        $this->info('🚀 Demonstração dos Services SOLID-compliant');
        $this->newLine();

        // Demonstrar UserQueryService
        $this->info('👥 UserQueryService (especializado em consultas de usuários):');
        $this->line('  - findByErpUserId()');
        $this->line('  - getUsersWithErpIntegration()');
        $this->line('  - isErpUserIdInUse()');
        $this->newLine();

        // Demonstrar ContactQueryService
        $this->info('📞 ContactQueryService (especializado em consultas de contatos):');
        $this->line('  - findByBusinessPartnerId()');
        $this->line('  - getContactsWithBusinessPartnerIntegration()');
        $this->line('  - isBusinessPartnerIdInUse()');
        $this->newLine();

        // Demonstrar ContactStatsService
        $this->info('📊 ContactStatsService (especializado em estatísticas):');
        $this->line('  - getContactStats()');
        $this->line('  - getEngagementMetrics()');
        $this->newLine();

        // Demonstrar ContactBusinessService
        $this->info('💼 ContactBusinessService (especializado em lógica de negócio):');
        $this->line('  - updateLastInteraction()');
        $this->line('  - addTag()');
        $this->line('  - removeTag()');
        $this->line('  - blacklistContact()');
        $this->line('  - removeFromBlacklist()');
        $this->line('  - updatePriority()');
        $this->newLine();

        // Mostrar benefícios da arquitetura SOLID
        $this->info('✅ Benefícios da arquitetura SOLID implementada:');
        $this->line('  🎯 Single Responsibility: Cada service tem uma responsabilidade específica');
        $this->line('  🔧 Open/Closed: Fácil extensão sem modificar código existente');
        $this->line('  🔄 Liskov Substitution: Services podem ser substituídos por implementações');
        $this->line('  📝 Interface Segregation: Interfaces pequenas e especializadas');
        $this->line('  🏗️ Dependency Inversion: Dependência de abstrações, não implementações');
        $this->newLine();

        // Mostrar como os models agora seguem SRP
        $this->info('📋 Models agora seguem Single Responsibility Principle:');
        $this->line('  - User: Apenas estrutura de dados, relacionamentos e scopes simples');
        $this->line('  - Contact: Apenas estrutura de dados, relacionamentos e scopes simples');
        $this->line('  - Lógica de negócio movida para services especializados');
        $this->line('  - Consultas complexas movidas para query services');
        $this->newLine();

        // Exemplo prático (se houver dados)
        $usersWithErp = $this->userQueryService->getUsersWithErpIntegration();
        $contactsWithERP = $this->contactQueryService->getContactsWithBusinessPartnerIntegration();

        $this->info('📈 Dados atuais no sistema:');
        $this->line('  - Usuários com integração ERP: ' . count($usersWithErp));
        $this->line('  - Contatos com Business Partner: ' . count($contactsWithERP));

        $this->newLine();
        $this->info('🎉 Demonstração concluída! Arquitetura SOLID implementada com sucesso.');

        return Command::SUCCESS;
    }
}
