<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            // Categorias principais
            [
                'name' => 'Vendas',
                'description' => 'Consultas sobre vendas, orçamentos e propostas comerciais',
                'color' => '#28a745',
                'priority' => 2,
                'estimated_time' => 20,
                'auto_responses' => [
                    'welcome_message' => 'Olá! Obrigado pelo interesse em nossos produtos. Como posso ajudá-lo hoje?',
                    'business_hours_message' => 'Nosso horário de atendimento comercial é de segunda a sexta, das 8h às 18h.',
                ],
                'requires_specialist' => false,
                'is_active' => true,
            ],
            [
                'name' => 'Suporte Técnico',
                'description' => 'Problemas técnicos, bugs e questões de funcionamento',
                'color' => '#dc3545',
                'priority' => 3,
                'estimated_time' => 45,
                'auto_responses' => [
                    'welcome_message' => 'Olá! Sou do suporte técnico. Vou ajudá-lo a resolver seu problema.',
                    'info_request' => 'Para um melhor atendimento, pode me informar seu nome e descrever o problema?',
                ],
                'requires_specialist' => true,
                'is_active' => true,
            ],
            [
                'name' => 'Financeiro',
                'description' => 'Questões financeiras, pagamentos, faturas e cobrança',
                'color' => '#ffc107',
                'priority' => 2,
                'estimated_time' => 15,
                'auto_responses' => [
                    'welcome_message' => 'Olá! Sou do departamento financeiro. Como posso ajudá-lo?',
                    'document_request' => 'Para consultas financeiras, preciso de seu CPF ou CNPJ.',
                ],
                'requires_specialist' => false,
                'is_active' => true,
            ],
            [
                'name' => 'Emergência',
                'description' => 'Atendimento de emergência - alta prioridade',
                'color' => '#ff0000',
                'priority' => 3,
                'estimated_time' => 5,
                'auto_responses' => [
                    'welcome_message' => '🚨 EMERGÊNCIA: Seu atendimento foi priorizado. Um especialista responderá em instantes.',
                ],
                'requires_specialist' => true,
                'is_active' => true,
            ],
            [
                'name' => 'Informações Gerais',
                'description' => 'Informações gerais, dúvidas básicas e direcionamento',
                'color' => '#6c757d',
                'priority' => 1,
                'estimated_time' => 10,
                'auto_responses' => [
                    'welcome_message' => 'Olá! Bem-vindo! Como posso ajudá-lo hoje?',
                    'menu_options' => 'Posso ajudá-lo com: 1️⃣ Vendas 2️⃣ Suporte 3️⃣ Financeiro 4️⃣ Outros',
                ],
                'requires_specialist' => false,
                'is_active' => true,
            ],
        ];

        foreach ($categories as $categoryData) {
            Category::updateOrCreate(
                ['name' => $categoryData['name']],
                $categoryData
            );
        }

        // Criar subcategorias para Suporte Técnico
        $supportCategory = Category::where('name', 'Suporte Técnico')->first();
        if ($supportCategory) {
            $subCategories = [
                [
                    'name' => 'Problemas de Login',
                    'description' => 'Dificuldades para acessar o sistema',
                    'color' => '#dc3545',
                    'parent_id' => $supportCategory->id,
                    'priority' => 2,
                    'estimated_time' => 10,
                    'requires_specialist' => false,
                    'is_active' => true,
                ],
                [
                    'name' => 'Bugs do Sistema',
                    'description' => 'Erros e comportamentos inesperados do sistema',
                    'color' => '#dc3545',
                    'parent_id' => $supportCategory->id,
                    'priority' => 3,
                    'estimated_time' => 60,
                    'requires_specialist' => true,
                    'is_active' => true,
                ],
                [
                    'name' => 'Configurações',
                    'description' => 'Ajuda com configurações e personalizações',
                    'color' => '#17a2b8',
                    'parent_id' => $supportCategory->id,
                    'priority' => 1,
                    'estimated_time' => 20,
                    'requires_specialist' => false,
                    'is_active' => true,
                ],
            ];

            foreach ($subCategories as $subCategoryData) {
                Category::updateOrCreate(
                    [
                        'name' => $subCategoryData['name'],
                        'parent_id' => $subCategoryData['parent_id']
                    ],
                    $subCategoryData
                );
            }
        }

        // Criar subcategorias para Vendas
        $salesCategory = Category::where('name', 'Vendas')->first();
        if ($salesCategory) {
            $salesSubCategories = [
                [
                    'name' => 'Novos Clientes',
                    'description' => 'Atendimento para prospects e novos clientes',
                    'color' => '#28a745',
                    'parent_id' => $salesCategory->id,
                    'priority' => 3,
                    'estimated_time' => 30,
                    'requires_specialist' => false,
                    'is_active' => true,
                ],
                [
                    'name' => 'Renovação',
                    'description' => 'Renovação de contratos e serviços existentes',
                    'color' => '#28a745',
                    'parent_id' => $salesCategory->id,
                    'priority' => 2,
                    'estimated_time' => 15,
                    'requires_specialist' => false,
                    'is_active' => true,
                ],
                [
                    'name' => 'Upgrade/Upsell',
                    'description' => 'Upgrade de planos e vendas adicionais',
                    'color' => '#ffc107',
                    'parent_id' => $salesCategory->id,
                    'priority' => 2,
                    'estimated_time' => 25,
                    'requires_specialist' => false,
                    'is_active' => true,
                ],
            ];

            foreach ($salesSubCategories as $subCategoryData) {
                Category::updateOrCreate(
                    [
                        'name' => $subCategoryData['name'],
                        'parent_id' => $subCategoryData['parent_id']
                    ],
                    $subCategoryData
                );
            }
        }
    }
}
