<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $roles = [
            [
                'name' => 'admin',
                'description' => 'Administrador do sistema com acesso total',
                'permissions' => [
                    'user.create', 'user.read', 'user.update', 'user.delete',
                    'role.create', 'role.read', 'role.update', 'role.delete',
                    'department.create', 'department.read', 'department.update', 'department.delete',
                    'category.create', 'category.read', 'category.update', 'category.delete',
                    'channel.create', 'channel.read', 'channel.update', 'channel.delete',
                    'conversation.create', 'conversation.read', 'conversation.update', 'conversation.delete',
                    'conversation.assign', 'conversation.transfer', 'conversation.close',
                    'message.create', 'message.read', 'message.update', 'message.delete',
                    'contact.create', 'contact.read', 'contact.update', 'contact.delete',
                    'contact.blacklist', 'contact.unblacklist',
                    'report.view', 'settings.manage', 'system.admin'
                ],
                'can_transfer' => true,
                'can_close_tickets' => true,
                'max_simultaneous_chats' => 999,
            ],
            [
                'name' => 'manager',
                'description' => 'Gerente de atendimento com permissões de supervisão',
                'permissions' => [
                    'user.read', 'user.update',
                    'department.read', 'department.update',
                    'category.read', 'category.update',
                    'channel.read',
                    'conversation.create', 'conversation.read', 'conversation.update',
                    'conversation.assign', 'conversation.transfer', 'conversation.close',
                    'message.create', 'message.read', 'message.update',
                    'contact.create', 'contact.read', 'contact.update',
                    'contact.blacklist', 'contact.unblacklist',
                    'report.view', 'team.manage'
                ],
                'can_transfer' => true,
                'can_close_tickets' => true,
                'max_simultaneous_chats' => 20,
            ],
            [
                'name' => 'agent',
                'description' => 'Atendente com permissões básicas de chat',
                'permissions' => [
                    'conversation.read', 'conversation.update',
                    'conversation.transfer',
                    'message.create', 'message.read',
                    'contact.read', 'contact.update',
                    'profile.update'
                ],
                'can_transfer' => true,
                'can_close_tickets' => false,
                'max_simultaneous_chats' => 5,
            ],
            [
                'name' => 'supervisor',
                'description' => 'Supervisor de atendimento',
                'permissions' => [
                    'user.read',
                    'conversation.create', 'conversation.read', 'conversation.update',
                    'conversation.assign', 'conversation.transfer', 'conversation.close',
                    'message.create', 'message.read', 'message.update',
                    'contact.create', 'contact.read', 'contact.update',
                    'report.view'
                ],
                'can_transfer' => true,
                'can_close_tickets' => true,
                'max_simultaneous_chats' => 10,
            ],
        ];

        foreach ($roles as $roleData) {
            Role::updateOrCreate(
                ['name' => $roleData['name']],
                $roleData
            );
        }
    }
}
