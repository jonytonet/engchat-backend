<?php

namespace App\Console\Commands;

use App\Services\WhatsAppService;
use Illuminate\Console\Command;

/**
 * Comando para testar funcionalidades do WhatsApp
 * 
 * Permite testar envio de mensagens, verificar configuração e templates.
 */
class TestWhatsAppCommand extends Command
{
    protected $signature = 'whatsapp:test 
                          {action : Ação a executar (config|send-text|send-template|templates)}
                          {--phone= : Número de telefone (para envio)}
                          {--message= : Mensagem de texto}
                          {--template= : Nome do template}';

    protected $description = 'Testa funcionalidades do WhatsApp';

    public function __construct(
        private WhatsAppService $whatsAppService
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        $action = $this->argument('action');

        return match ($action) {
            'config' => $this->testConfiguration(),
            'send-text' => $this->sendTextMessage(),
            'send-template' => $this->sendTemplateMessage(),
            'templates' => $this->listTemplates(),
            default => $this->invalidAction($action)
        };
    }

    private function testConfiguration(): int
    {
        $this->info('🔍 Verificando configuração do WhatsApp...');

        $result = $this->whatsAppService->checkConfiguration();

        if ($result->success) {
            $this->info('✅ Configuração válida!');

            if ($result->data) {
                $profile = $result->data;
                $this->line("📱 Número: " . ($profile['display_phone_number'] ?? 'N/A'));
                $this->line("✅ Verificado: " . ($profile['verified_name'] ?? 'N/A'));
                $this->line("⭐ Qualidade: " . ($profile['quality_rating'] ?? 'N/A'));
            }

            return self::SUCCESS;
        }

        $this->error('❌ Erro na configuração:');
        $this->error($result->error);

        return self::FAILURE;
    }

    private function sendTextMessage(): int
    {
        $phone = $this->option('phone');
        $message = $this->option('message');

        if (!$phone) {
            $phone = $this->ask('📱 Número de telefone (com DDI):');
        }

        if (!$message) {
            $message = $this->ask('💬 Mensagem a enviar:');
        }

        if (!$phone || !$message) {
            $this->error('❌ Telefone e mensagem são obrigatórios');
            return self::FAILURE;
        }

        $this->info("📤 Enviando mensagem para {$phone}...");

        $result = $this->whatsAppService->sendTextMessage($phone, $message);

        if ($result->success) {
            $this->info('✅ Mensagem enviada com sucesso!');
            $this->line("🆔 ID: {$result->messageId}");
            return self::SUCCESS;
        }

        $this->error('❌ Erro ao enviar mensagem:');
        $this->error($result->error);

        return self::FAILURE;
    }

    private function sendTemplateMessage(): int
    {
        $phone = $this->option('phone');
        $template = $this->option('template');

        if (!$phone) {
            $phone = $this->ask('📱 Número de telefone (com DDI):');
        }

        if (!$template) {
            $template = $this->ask('📋 Nome do template:');
        }

        if (!$phone || !$template) {
            $this->error('❌ Telefone e template são obrigatórios');
            return self::FAILURE;
        }

        $this->info("📤 Enviando template '{$template}' para {$phone}...");

        $result = $this->whatsAppService->sendTemplateMessage($phone, $template);

        if ($result->success) {
            $this->info('✅ Template enviado com sucesso!');
            $this->line("🆔 ID: {$result->messageId}");
            return self::SUCCESS;
        }

        $this->error('❌ Erro ao enviar template:');
        $this->error($result->error);

        return self::FAILURE;
    }

    private function listTemplates(): int
    {
        $this->info('📋 Obtendo templates disponíveis...');

        $result = $this->whatsAppService->getAvailableTemplates();

        if ($result->success) {
            $templates = $result->data['data'] ?? [];

            if (empty($templates)) {
                $this->warn('⚠️ Nenhum template encontrado');
                return self::SUCCESS;
            }

            $this->info('✅ Templates disponíveis:');
            $this->line('');

            foreach ($templates as $template) {
                $this->line("📋 {$template['name']}");
                $this->line("   Status: {$template['status']}");
                $this->line("   Categoria: {$template['category']}");
                $this->line("   Idioma: {$template['language']}");

                if (!empty($template['components'])) {
                    $this->line("   Componentes:");
                    foreach ($template['components'] as $component) {
                        $this->line("     - {$component['type']}");
                    }
                }

                $this->line('');
            }

            return self::SUCCESS;
        }

        $this->error('❌ Erro ao obter templates:');
        $this->error($result->error);

        return self::FAILURE;
    }

    private function invalidAction(string $action): int
    {
        $this->error("❌ Ação inválida: {$action}");
        $this->line('');
        $this->line('Ações disponíveis:');
        $this->line('  config       - Verifica configuração');
        $this->line('  send-text    - Envia mensagem de texto');
        $this->line('  send-template - Envia template');
        $this->line('  templates    - Lista templates disponíveis');
        $this->line('');
        $this->line('Exemplos:');
        $this->line('  php artisan whatsapp:test config');
        $this->line('  php artisan whatsapp:test send-text --phone=5511999999999 --message="Olá!"');
        $this->line('  php artisan whatsapp:test send-template --phone=5511999999999 --template="hello_world"');
        $this->line('  php artisan whatsapp:test templates');

        return self::FAILURE;
    }
}
