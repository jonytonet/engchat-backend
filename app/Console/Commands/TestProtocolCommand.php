<?php

namespace App\Console\Commands;

use App\Services\ProtocolService;
use App\DTOs\CreateProtocolDTO;
use App\Models\Contact;
use Illuminate\Console\Command;

/**
 * Comando para testar funcionalidades de protocolos
 * 
 * Permite criar e gerenciar protocolos via linha de comando.
 */
class TestProtocolCommand extends Command
{
    protected $signature = 'protocol:test 
                          {action : Ação a executar (create|list|show|close|reopen|stats)}
                          {--contact= : ID do contato (para criação)}
                          {--protocol= : ID do protocolo (para operações específicas)}
                          {--notes= : Anotações/motivo}';

    protected $description = 'Testa funcionalidades de protocolos';

    public function __construct(
        private ProtocolService $protocolService
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        $action = $this->argument('action');

        return match($action) {
            'create' => $this->createProtocol(),
            'list' => $this->listProtocols(),
            'show' => $this->showProtocol(),
            'close' => $this->closeProtocol(),
            'reopen' => $this->reopenProtocol(),
            'stats' => $this->showStatistics(),
            default => $this->invalidAction($action)
        };
    }

    private function createProtocol(): int
    {
        $contactId = $this->option('contact');

        if (!$contactId) {
            $contactId = $this->ask('📱 ID do contato:');
        }

        if (!$contactId) {
            $this->error('❌ ID do contato é obrigatório');
            return self::FAILURE;
        }

        // Verificar se contato existe
        $contact = Contact::find($contactId);
        if (!$contact) {
            $this->error("❌ Contato {$contactId} não encontrado");
            return self::FAILURE;
        }

        $description = $this->ask('📝 Descrição do protocolo (opcional):') ?? '';

        try {
            $dto = new CreateProtocolDTO(
                contactId: (int) $contactId,
                description: $description
            );

            $protocol = $this->protocolService->createProtocol($dto);

            $this->info('✅ Protocolo criado com sucesso!');
            $this->displayProtocol($protocol);

            return self::SUCCESS;

        } catch (\Exception $e) {
            $this->error('❌ Erro ao criar protocolo: ' . $e->getMessage());
            return self::FAILURE;
        }
    }

    private function listProtocols(): int
    {
        try {
            $filters = [];
            
            // Adicionar filtros se fornecidos
            if ($status = $this->ask('🔍 Filtrar por status (open/closed/resolved)?')) {
                $filters['status'] = $status;
            }

            if ($contactId = $this->ask('📱 Filtrar por ID do contato?')) {
                $filters['contact_id'] = $contactId;
            }

            $filters['per_page'] = 10; // Limitar para visualização

            $protocols = $this->protocolService->listProtocols($filters);

            if ($protocols->isEmpty()) {
                $this->warn('⚠️ Nenhum protocolo encontrado');
                return self::SUCCESS;
            }

            $this->info('📋 Protocolos encontrados:');
            $this->line('');

            foreach ($protocols->items() as $protocol) {
                $this->displayProtocolSummary($protocol);
            }

            $this->line('');
            $this->info("📄 Página {$protocols->currentPage()} de {$protocols->lastPage()}");
            $this->info("📊 Total: {$protocols->total()} protocolos");

            return self::SUCCESS;

        } catch (\Exception $e) {
            $this->error('❌ Erro ao listar protocolos: ' . $e->getMessage());
            return self::FAILURE;
        }
    }

    private function showProtocol(): int
    {
        $protocolId = $this->option('protocol');

        if (!$protocolId) {
            $protocolId = $this->ask('🆔 ID do protocolo:');
        }

        if (!$protocolId) {
            $this->error('❌ ID do protocolo é obrigatório');
            return self::FAILURE;
        }

        try {
            $protocol = $this->protocolService->findProtocolById((int) $protocolId, true);

            if (!$protocol) {
                $this->error("❌ Protocolo {$protocolId} não encontrado");
                return self::FAILURE;
            }

            $this->info('📋 Detalhes do protocolo:');
            $this->displayProtocol($protocol);

            return self::SUCCESS;

        } catch (\Exception $e) {
            $this->error('❌ Erro ao buscar protocolo: ' . $e->getMessage());
            return self::FAILURE;
        }
    }

    private function closeProtocol(): int
    {
        $protocolId = $this->option('protocol');

        if (!$protocolId) {
            $protocolId = $this->ask('🆔 ID do protocolo:');
        }

        if (!$protocolId) {
            $this->error('❌ ID do protocolo é obrigatório');
            return self::FAILURE;
        }

        $notes = $this->option('notes') ?? $this->ask('📝 Notas de fechamento (opcional):');

        try {
            $protocol = $this->protocolService->closeProtocol((int) $protocolId, $notes);

            if (!$protocol) {
                $this->error("❌ Protocolo {$protocolId} não encontrado");
                return self::FAILURE;
            }

            $this->info('✅ Protocolo fechado com sucesso!');
            $this->displayProtocol($protocol);

            return self::SUCCESS;

        } catch (\Exception $e) {
            $this->error('❌ Erro ao fechar protocolo: ' . $e->getMessage());
            return self::FAILURE;
        }
    }

    private function reopenProtocol(): int
    {
        $protocolId = $this->option('protocol');

        if (!$protocolId) {
            $protocolId = $this->ask('🆔 ID do protocolo:');
        }

        if (!$protocolId) {
            $this->error('❌ ID do protocolo é obrigatório');
            return self::FAILURE;
        }

        $reason = $this->option('notes') ?? $this->ask('📝 Motivo da reabertura:');

        if (!$reason) {
            $this->error('❌ Motivo da reabertura é obrigatório');
            return self::FAILURE;
        }

        try {
            $protocol = $this->protocolService->reopenProtocol((int) $protocolId, $reason);

            if (!$protocol) {
                $this->error("❌ Protocolo {$protocolId} não encontrado");
                return self::FAILURE;
            }

            $this->info('✅ Protocolo reaberto com sucesso!');
            $this->displayProtocol($protocol);

            return self::SUCCESS;

        } catch (\Exception $e) {
            $this->error('❌ Erro ao reabrir protocolo: ' . $e->getMessage());
            return self::FAILURE;
        }
    }

    private function showStatistics(): int
    {
        try {
            $stats = $this->protocolService->getProtocolStatistics();

            $this->info('📊 Estatísticas de Protocolos:');
            $this->line('');

            $this->info('📈 Por Status:');
            foreach ($stats['by_status'] as $status => $count) {
                $this->line("   {$status}: {$count}");
            }

            $this->line('');
            $this->info('🚨 Por Prioridade:');
            foreach ($stats['by_priority'] as $priority => $count) {
                $this->line("   {$priority}: {$count}");
            }

            $this->line('');
            $this->info("🔥 Protocolos urgentes: " . $stats['urgent_count']);
            $this->info("📋 Protocolos não atribuídos: " . $stats['unassigned_count']);

            return self::SUCCESS;

        } catch (\Exception $e) {
            $this->error('❌ Erro ao obter estatísticas: ' . $e->getMessage());
            return self::FAILURE;
        }
    }

    private function displayProtocol($protocol): void
    {
        $this->line('');
        $this->line("🆔 ID: {$protocol->id}");
        $this->line("📋 Número: {$protocol->protocolNumber}");
        $this->line("📱 Contato ID: {$protocol->contactId}");
        $this->line("📊 Status: {$protocol->status}");
        $this->line("⚡ Prioridade: {$protocol->priority}");
        
        if ($protocol->description) {
            $this->line("📝 Descrição: {$protocol->description}");
        }
        
        if ($protocol->resolutionNotes) {
            $this->line("📋 Notas: {$protocol->resolutionNotes}");
        }
        
        $this->line("🕐 Criado em: " . $protocol->createdAt->format('d/m/Y H:i:s'));
        
        if ($protocol->closedAt) {
            $this->line("🔒 Fechado em: " . $protocol->closedAt->format('d/m/Y H:i:s'));
        }
        
        $this->line('');
    }

    private function displayProtocolSummary($protocol): void
    {
        $statusIcon = match($protocol->status) {
            'open' => '🟢',
            'closed' => '🔴',
            'resolved' => '✅',
            'cancelled' => '❌',
            default => '⚪'
        };

        $priorityIcon = match($protocol->priority) {
            'low' => '🟢',
            'medium' => '🟡',
            'high' => '🟠',
            'urgent' => '🔴',
            default => '⚪'
        };

        $this->line("{$statusIcon} {$protocol->protocolNumber} | {$priorityIcon} {$protocol->priority} | Contato: {$protocol->contactId}");
    }

    private function invalidAction(string $action): int
    {
        $this->error("❌ Ação inválida: {$action}");
        $this->line('');
        $this->line('Ações disponíveis:');
        $this->line('  create     - Cria novo protocolo');
        $this->line('  list       - Lista protocolos');
        $this->line('  show       - Exibe protocolo específico');
        $this->line('  close      - Fecha protocolo');
        $this->line('  reopen     - Reabre protocolo');
        $this->line('  stats      - Exibe estatísticas');
        $this->line('');
        $this->line('Exemplos:');
        $this->line('  php artisan protocol:test create --contact=1');
        $this->line('  php artisan protocol:test show --protocol=1');
        $this->line('  php artisan protocol:test close --protocol=1 --notes="Resolvido"');
        $this->line('  php artisan protocol:test stats');

        return self::FAILURE;
    }
}
