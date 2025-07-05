<?php

namespace Database\Seeders;

use App\Models\MessageTemplate;
use App\Models\QueueRule;
use App\Models\Role;
use App\Models\Department;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class QueueSystemSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::transaction(function () {
            $this->seedRoles();
            $this->seedDepartments();
            $this->seedUsers();
            $this->seedMessageTemplates();
            $this->seedQueueRules();
        });
    }

    private function seedRoles(): void
    {
        $roles = [
            [
                'name' => 'admin',
                'description' => 'Administrador do sistema',
                'permissions' => [
                    'admin.access',
                    'users.manage',
                    'departments.manage',
                    'queue.manage',
                    'reports.view'
                ],
                'can_transfer' => true,
                'can_close_tickets' => true,
                'max_simultaneous_chats' => 10,
            ],
            [
                'name' => 'manager',
                'description' => 'Gerente de atendimento',
                'permissions' => [
                    'team.manage',
                    'queue.view',
                    'reports.view',
                    'transfers.approve'
                ],
                'can_transfer' => true,
                'can_close_tickets' => true,
                'max_simultaneous_chats' => 8,
            ],
            [
                'name' => 'agent',
                'description' => 'Agente de atendimento',
                'permissions' => [
                    'chat.view',
                    'chat.respond',
                    'tickets.close'
                ],
                'can_transfer' => true,
                'can_close_tickets' => true,
                'max_simultaneous_chats' => 5,
            ],
            [
                'name' => 'specialist',
                'description' => 'Especialista técnico',
                'permissions' => [
                    'chat.view',
                    'chat.respond',
                    'tickets.close',
                    'escalations.handle'
                ],
                'can_transfer' => true,
                'can_close_tickets' => true,
                'max_simultaneous_chats' => 3,
            ],
        ];

        foreach ($roles as $roleData) {
            Role::updateOrCreate(
                ['name' => $roleData['name']],
                $roleData
            );
        }
    }

    private function seedDepartments(): void
    {
        $departments = [
            [
                'name' => 'Atendimento Geral',
                'description' => 'Atendimento inicial e direcionamento',
                'is_active' => true,
                'working_hours' => [
                    'monday' => ['start' => '08:00', 'end' => '18:00'],
                    'tuesday' => ['start' => '08:00', 'end' => '18:00'],
                    'wednesday' => ['start' => '08:00', 'end' => '18:00'],
                    'thursday' => ['start' => '08:00', 'end' => '18:00'],
                    'friday' => ['start' => '08:00', 'end' => '18:00'],
                    'saturday' => ['start' => '09:00', 'end' => '14:00'],
                ],
                'auto_assignment_enabled' => true,
            ],
            [
                'name' => 'Suporte Técnico',
                'description' => 'Suporte técnico especializado',
                'is_active' => true,
                'working_hours' => [
                    'monday' => ['start' => '08:00', 'end' => '18:00'],
                    'tuesday' => ['start' => '08:00', 'end' => '18:00'],
                    'wednesday' => ['start' => '08:00', 'end' => '18:00'],
                    'thursday' => ['start' => '08:00', 'end' => '18:00'],
                    'friday' => ['start' => '08:00', 'end' => '18:00'],
                ],
                'auto_assignment_enabled' => true,
            ],
            [
                'name' => 'Vendas',
                'description' => 'Equipe de vendas e relacionamento',
                'is_active' => true,
                'working_hours' => [
                    'monday' => ['start' => '08:00', 'end' => '20:00'],
                    'tuesday' => ['start' => '08:00', 'end' => '20:00'],
                    'wednesday' => ['start' => '08:00', 'end' => '20:00'],
                    'thursday' => ['start' => '08:00', 'end' => '20:00'],
                    'friday' => ['start' => '08:00', 'end' => '20:00'],
                    'saturday' => ['start' => '09:00', 'end' => '17:00'],
                ],
                'auto_assignment_enabled' => true,
            ],
        ];

        foreach ($departments as $deptData) {
            Department::updateOrCreate(
                ['name' => $deptData['name']],
                $deptData
            );
        }
    }

    private function seedUsers(): void
    {
        $adminRole = Role::where('name', 'admin')->first();
        $managerRole = Role::where('name', 'manager')->first();
        $agentRole = Role::where('name', 'agent')->first();
        $generalDept = Department::where('name', 'Atendimento Geral')->first();

        // Admin user
        User::updateOrCreate(
            ['email' => 'admin@engchat.com'],
            [
                'name' => 'Administrador EngChat',
                'password' => bcrypt('admin123'),
                'role_id' => $adminRole->id,
                'department_id' => $generalDept->id,
                'status' => 'online',
                'is_active' => true,
                'timezone' => 'America/Sao_Paulo',
                'language' => 'pt-BR',
            ]
        );

        // System user for queue operations
        User::updateOrCreate(
            ['email' => 'system@engchat.com'],
            [
                'name' => 'Sistema EngChat',
                'password' => bcrypt('system123'),
                'role_id' => $agentRole->id,
                'department_id' => $generalDept->id,
                'status' => 'online',
                'is_active' => true,
                'timezone' => 'America/Sao_Paulo',
                'language' => 'pt-BR',
            ]
        );
    }

    private function seedMessageTemplates(): void
    {
        $systemUser = User::where('email', 'system@engchat.com')->first();

        $templates = [
            [
                'name' => 'Entrada na Fila',
                'display_name' => 'Mensagem de Espera',
                'template_code' => 'queue_welcome',
                'language' => 'pt_BR',
                'category' => 'utility',
                'approval_status' => 'approved',
                'body_content' => "Olá {{1}}! 👋\n\nRecebemos sua mensagem e em breve um de nossos atendentes estará com você.\n\n📋 Você está na posição **{{2}}** da fila\n⏱️ Tempo estimado de espera: **{{3}} minutos**\n\nAguarde um momento, não saia do chat! 😊",
                'parameters' => [
                    ['position' => 1, 'name' => 'contact_name', 'default_value' => '{{contact.name}}', 'required' => true, 'type' => 'text'],
                    ['position' => 2, 'name' => 'queue_position', 'required' => true, 'type' => 'number'],
                    ['position' => 3, 'name' => 'estimated_wait', 'required' => true, 'type' => 'number']
                ],
                'variables_count' => 3,
                'is_global' => true,
                'is_active' => true,
                'created_by' => $systemUser->id,
            ],
            [
                'name' => 'Atualização de Posição',
                'display_name' => 'Update da Fila',
                'template_code' => 'queue_position_update',
                'language' => 'pt_BR',
                'category' => 'utility',
                'approval_status' => 'approved',
                'body_content' => "📍 **Atualização da Fila**\n\nOlá {{1}}!\n\nVocê agora está na posição **{{2}}** da fila.\n⏱️ Tempo estimado: **{{3}} minutos**\n\nObrigado pela paciência! 🙏",
                'parameters' => [
                    ['position' => 1, 'name' => 'contact_name', 'default_value' => '{{contact.name}}', 'required' => true, 'type' => 'text'],
                    ['position' => 2, 'name' => 'queue_position', 'required' => true, 'type' => 'number'],
                    ['position' => 3, 'name' => 'estimated_wait', 'required' => true, 'type' => 'number']
                ],
                'variables_count' => 3,
                'is_global' => true,
                'is_active' => true,
                'created_by' => $systemUser->id,
            ],
            [
                'name' => 'Agente Conectado',
                'display_name' => 'Conexão com Agente',
                'template_code' => 'agent_connected',
                'language' => 'pt_BR',
                'category' => 'utility',
                'approval_status' => 'approved',
                'body_content' => "✅ **Conectado com atendente!**\n\nOlá {{1}}!\n\n{{2}} da equipe {{3}} está agora disponível para atendê-lo.\n\nComo posso ajudá-lo hoje? 😊",
                'parameters' => [
                    ['position' => 1, 'name' => 'contact_name', 'default_value' => '{{contact.name}}', 'required' => true, 'type' => 'text'],
                    ['position' => 2, 'name' => 'agent_name', 'default_value' => '{{agent.name}}', 'required' => true, 'type' => 'text'],
                    ['position' => 3, 'name' => 'department_name', 'default_value' => '{{department.name}}', 'required' => true, 'type' => 'text']
                ],
                'variables_count' => 3,
                'is_global' => true,
                'is_active' => true,
                'created_by' => $systemUser->id,
            ],
            [
                'name' => 'Fila Cheia',
                'display_name' => 'Fila Lotada',
                'template_code' => 'queue_full',
                'language' => 'pt_BR',
                'category' => 'utility',
                'approval_status' => 'approved',
                'body_content' => "⚠️ **Fila Temporariamente Cheia**\n\nOlá {{1}}!\n\nNo momento nossa fila de atendimento está lotada.\n\nPor favor, tente novamente em alguns minutos ou deixe sua mensagem que retornaremos em breve.\n\nObrigado pela compreensão! 🙏",
                'parameters' => [
                    ['position' => 1, 'name' => 'contact_name', 'default_value' => '{{contact.name}}', 'required' => true, 'type' => 'text']
                ],
                'variables_count' => 1,
                'is_global' => true,
                'is_active' => true,
                'created_by' => $systemUser->id,
            ],
            [
                'name' => 'Fora do Horário',
                'display_name' => 'Horário de Atendimento',
                'template_code' => 'out_of_hours',
                'language' => 'pt_BR',
                'category' => 'utility',
                'approval_status' => 'approved',
                'body_content' => "🕐 **Fora do Horário de Atendimento**\n\nOlá {{1}}!\n\nNo momento estamos fora do horário de atendimento.\n\n🕐 **Horário:** Segunda a Sexta das 8h às 18h\n🕐 **Sábado:** das 9h às 14h\n\nDeixe sua mensagem que retornaremos no próximo horário útil!\n\nObrigado! 😊",
                'parameters' => [
                    ['position' => 1, 'name' => 'contact_name', 'default_value' => '{{contact.name}}', 'required' => true, 'type' => 'text']
                ],
                'variables_count' => 1,
                'is_global' => true,
                'is_active' => true,
                'created_by' => $systemUser->id,
            ],
        ];

        foreach ($templates as $templateData) {
            MessageTemplate::updateOrCreate(
                ['template_code' => $templateData['template_code']],
                $templateData
            );
        }
    }

    private function seedQueueRules(): void
    {
        $generalDept = Department::where('name', 'Atendimento Geral')->first();
        $supportDept = Department::where('name', 'Suporte Técnico')->first();
        $salesDept = Department::where('name', 'Vendas')->first();
        $systemUser = User::where('email', 'system@engchat.com')->first();

        $welcomeTemplate = MessageTemplate::where('template_code', 'queue_welcome')->first();
        $positionTemplate = MessageTemplate::where('template_code', 'queue_position_update')->first();
        $agentTemplate = MessageTemplate::where('template_code', 'agent_connected')->first();
        $outOfHoursTemplate = MessageTemplate::where('template_code', 'out_of_hours')->first();

        $queueRules = [
            [
                'department_id' => $generalDept->id,
                'max_queue_size' => 50,
                'max_wait_time_minutes' => 30,
                'first_notification_after_minutes' => 2,
                'notification_interval_minutes' => 5,
                'max_notifications' => 6,
                'welcome_template_id' => $welcomeTemplate->id,
                'position_update_template_id' => $positionTemplate->id,
                'assigned_template_id' => $agentTemplate->id,
                'out_of_hours_template_id' => $outOfHoursTemplate->id,
                'priority_rules' => [
                    'urgent' => 4.0,
                    'high' => 3.0,
                    'medium' => 2.0,
                    'low' => 1.0
                ],
                'vip_priority_enabled' => true,
                'working_hours' => [
                    'monday' => ['start' => '08:00', 'end' => '18:00'],
                    'tuesday' => ['start' => '08:00', 'end' => '18:00'],
                    'wednesday' => ['start' => '08:00', 'end' => '18:00'],
                    'thursday' => ['start' => '08:00', 'end' => '18:00'],
                    'friday' => ['start' => '08:00', 'end' => '18:00'],
                    'saturday' => ['start' => '09:00', 'end' => '14:00'],
                ],
                'auto_assignment_enabled' => true,
                'assignment_algorithm' => 'least_busy',
                'escalation_enabled' => true,
                'escalation_time_minutes' => 15,
                'escalation_department_id' => $supportDept->id,
                'is_active' => true,
                'created_by' => $systemUser->id,
            ],
            [
                'department_id' => $supportDept->id,
                'max_queue_size' => 20,
                'max_wait_time_minutes' => 45,
                'first_notification_after_minutes' => 3,
                'notification_interval_minutes' => 7,
                'max_notifications' => 5,
                'welcome_template_id' => $welcomeTemplate->id,
                'position_update_template_id' => $positionTemplate->id,
                'assigned_template_id' => $agentTemplate->id,
                'out_of_hours_template_id' => $outOfHoursTemplate->id,
                'priority_rules' => [
                    'urgent' => 5.0,
                    'high' => 4.0,
                    'medium' => 2.5,
                    'low' => 1.0
                ],
                'vip_priority_enabled' => true,
                'working_hours' => [
                    'monday' => ['start' => '08:00', 'end' => '18:00'],
                    'tuesday' => ['start' => '08:00', 'end' => '18:00'],
                    'wednesday' => ['start' => '08:00', 'end' => '18:00'],
                    'thursday' => ['start' => '08:00', 'end' => '18:00'],
                    'friday' => ['start' => '08:00', 'end' => '18:00'],
                ],
                'auto_assignment_enabled' => true,
                'assignment_algorithm' => 'skill_based',
                'escalation_enabled' => false,
                'escalation_time_minutes' => 30,
                'is_active' => true,
                'created_by' => $systemUser->id,
            ],
            [
                'department_id' => $salesDept->id,
                'max_queue_size' => 30,
                'max_wait_time_minutes' => 20,
                'first_notification_after_minutes' => 1,
                'notification_interval_minutes' => 3,
                'max_notifications' => 8,
                'welcome_template_id' => $welcomeTemplate->id,
                'position_update_template_id' => $positionTemplate->id,
                'assigned_template_id' => $agentTemplate->id,
                'out_of_hours_template_id' => $outOfHoursTemplate->id,
                'priority_rules' => [
                    'urgent' => 6.0,
                    'high' => 4.5,
                    'medium' => 3.0,
                    'low' => 1.5
                ],
                'vip_priority_enabled' => true,
                'working_hours' => [
                    'monday' => ['start' => '08:00', 'end' => '20:00'],
                    'tuesday' => ['start' => '08:00', 'end' => '20:00'],
                    'wednesday' => ['start' => '08:00', 'end' => '20:00'],
                    'thursday' => ['start' => '08:00', 'end' => '20:00'],
                    'friday' => ['start' => '08:00', 'end' => '20:00'],
                    'saturday' => ['start' => '09:00', 'end' => '17:00'],
                ],
                'auto_assignment_enabled' => true,
                'assignment_algorithm' => 'round_robin',
                'escalation_enabled' => true,
                'escalation_time_minutes' => 10,
                'escalation_department_id' => $generalDept->id,
                'is_active' => true,
                'created_by' => $systemUser->id,
            ],
        ];

        foreach ($queueRules as $ruleData) {
            QueueRule::updateOrCreate(
                ['department_id' => $ruleData['department_id']],
                $ruleData
            );
        }
    }
}
