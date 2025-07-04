<?php

namespace Database\Seeders;

use App\Models\Channel;
use Illuminate\Database\Seeder;

class ChannelSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $channels = [
            [
                'name' => 'WhatsApp Business',
                'type' => 'whatsapp',
                'configuration' => [
                    'phone_number' => '554133808848',
                    'business_account_id' => 'your_business_account_id',
                    'api_version' => 'v17.0',
                    'webhook_url' => '/api/webhooks/whatsapp',
                    'verify_token' => 'engchat_whatsapp_verify_token',
                    'access_token' => 'your_whatsapp_access_token',
                ],
                'is_active' => true,
                'priority' => 1,
                'working_hours' => [
                    'monday' => ['start' => '08:00', 'end' => '18:00'],
                    'tuesday' => ['start' => '08:00', 'end' => '18:00'],
                    'wednesday' => ['start' => '08:00', 'end' => '18:00'],
                    'thursday' => ['start' => '08:00', 'end' => '18:00'],
                    'friday' => ['start' => '08:00', 'end' => '18:00'],
                    'saturday' => ['start' => '09:00', 'end' => '14:00'],
                ],
                'auto_response_enabled' => true,
            ],
            [
                'name' => 'Chat Web',
                'type' => 'web',
                'configuration' => [
                    'widget_color' => '#667eea',
                    'welcome_message' => 'Olá! Como posso ajudá-lo hoje?',
                    'offline_message' => 'No momento estamos offline. Deixe sua mensagem que retornaremos em breve.',
                    'position' => 'bottom-right',
                    'show_agent_avatar' => true,
                    'show_agent_name' => true,
                    'enable_file_upload' => true,
                    'max_file_size' => 10, // MB
                ],
                'is_active' => true,
                'priority' => 2,
                'working_hours' => [
                    'monday' => ['start' => '08:00', 'end' => '18:00'],
                    'tuesday' => ['start' => '08:00', 'end' => '18:00'],
                    'wednesday' => ['start' => '08:00', 'end' => '18:00'],
                    'thursday' => ['start' => '08:00', 'end' => '18:00'],
                    'friday' => ['start' => '08:00', 'end' => '18:00'],
                ],
                'auto_response_enabled' => true,
            ],
            [
                'name' => 'Telegram Bot',
                'type' => 'telegram',
                'configuration' => [
                    'bot_token' => 'your_telegram_bot_token',
                    'bot_username' => 'your_bot_username',
                    'webhook_url' => '/api/webhooks/telegram',
                    'commands' => [
                        '/start' => 'Bem-vindo ao nosso atendimento!',
                        '/help' => 'Como posso ajudá-lo? Digite sua mensagem.',
                        '/suporte' => 'Você será direcionado para o suporte técnico.',
                        '/vendas' => 'Você será direcionado para o time de vendas.',
                    ],
                ],
                'is_active' => false, // Desabilitado por padrão
                'priority' => 3,
                'working_hours' => [
                    'monday' => ['start' => '08:00', 'end' => '18:00'],
                    'tuesday' => ['start' => '08:00', 'end' => '18:00'],
                    'wednesday' => ['start' => '08:00', 'end' => '18:00'],
                    'thursday' => ['start' => '08:00', 'end' => '18:00'],
                    'friday' => ['start' => '08:00', 'end' => '18:00'],
                ],
                'auto_response_enabled' => true,
            ],
            [
                'name' => 'Facebook Messenger',
                'type' => 'facebook',
                'configuration' => [
                    'page_id' => 'your_facebook_page_id',
                    'page_access_token' => 'your_page_access_token',
                    'app_secret' => 'your_app_secret',
                    'verify_token' => 'engchat_facebook_verify_token',
                    'webhook_url' => '/api/webhooks/facebook',
                ],
                'is_active' => false, // Desabilitado por padrão
                'priority' => 4,
                'working_hours' => [
                    'monday' => ['start' => '08:00', 'end' => '18:00'],
                    'tuesday' => ['start' => '08:00', 'end' => '18:00'],
                    'wednesday' => ['start' => '08:00', 'end' => '18:00'],
                    'thursday' => ['start' => '08:00', 'end' => '18:00'],
                    'friday' => ['start' => '08:00', 'end' => '18:00'],
                ],
                'auto_response_enabled' => true,
            ],
            [
                'name' => 'Instagram Direct',
                'type' => 'instagram',
                'configuration' => [
                    'instagram_account_id' => 'your_instagram_account_id',
                    'access_token' => 'your_instagram_access_token',
                    'webhook_url' => '/api/webhooks/instagram',
                ],
                'is_active' => false, // Desabilitado por padrão
                'priority' => 5,
                'working_hours' => [
                    'monday' => ['start' => '08:00', 'end' => '18:00'],
                    'tuesday' => ['start' => '08:00', 'end' => '18:00'],
                    'wednesday' => ['start' => '08:00', 'end' => '18:00'],
                    'thursday' => ['start' => '08:00', 'end' => '18:00'],
                    'friday' => ['start' => '08:00', 'end' => '18:00'],
                ],
                'auto_response_enabled' => true,
            ],
        ];

        foreach ($channels as $channelData) {
            Channel::updateOrCreate(
                ['name' => $channelData['name']],
                $channelData
            );
        }
    }
}
