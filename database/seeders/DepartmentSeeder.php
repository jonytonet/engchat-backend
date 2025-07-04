<?php

namespace Database\Seeders;

use App\Models\Department;
use Illuminate\Database\Seeder;

class DepartmentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $departments = [
            [
                'name' => 'Atendimento ao Cliente',
                'description' => 'Departamento responsável pelo atendimento geral aos clientes',
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
                'description' => 'Departamento especializado em suporte técnico e resolução de problemas',
                'is_active' => true,
                'working_hours' => [
                    'monday' => ['start' => '07:00', 'end' => '19:00'],
                    'tuesday' => ['start' => '07:00', 'end' => '19:00'],
                    'wednesday' => ['start' => '07:00', 'end' => '19:00'],
                    'thursday' => ['start' => '07:00', 'end' => '19:00'],
                    'friday' => ['start' => '07:00', 'end' => '19:00'],
                    'saturday' => ['start' => '08:00', 'end' => '16:00'],
                    'sunday' => ['start' => '08:00', 'end' => '16:00'],
                ],
                'auto_assignment_enabled' => true,
            ],
            [
                'name' => 'Vendas',
                'description' => 'Departamento de vendas e relacionamento comercial',
                'is_active' => true,
                'working_hours' => [
                    'monday' => ['start' => '08:00', 'end' => '18:00'],
                    'tuesday' => ['start' => '08:00', 'end' => '18:00'],
                    'wednesday' => ['start' => '08:00', 'end' => '18:00'],
                    'thursday' => ['start' => '08:00', 'end' => '18:00'],
                    'friday' => ['start' => '08:00', 'end' => '18:00'],
                    'saturday' => ['start' => '09:00', 'end' => '13:00'],
                ],
                'auto_assignment_enabled' => true,
            ],
            [
                'name' => 'Financeiro',
                'description' => 'Departamento financeiro e cobrança',
                'is_active' => true,
                'working_hours' => [
                    'monday' => ['start' => '08:00', 'end' => '17:00'],
                    'tuesday' => ['start' => '08:00', 'end' => '17:00'],
                    'wednesday' => ['start' => '08:00', 'end' => '17:00'],
                    'thursday' => ['start' => '08:00', 'end' => '17:00'],
                    'friday' => ['start' => '08:00', 'end' => '17:00'],
                ],
                'auto_assignment_enabled' => false,
            ],
            [
                'name' => 'Emergência 24/7',
                'description' => 'Atendimento de emergência 24 horas por dia, 7 dias por semana',
                'is_active' => true,
                'working_hours' => null, // 24/7 - sem restrições
                'auto_assignment_enabled' => true,
            ],
        ];

        foreach ($departments as $departmentData) {
            Department::updateOrCreate(
                ['name' => $departmentData['name']],
                $departmentData
            );
        }
    }
}
