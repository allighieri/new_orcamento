<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Plan;

class PlanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $plans = [
            [
                'name' => 'Bronze',
                'description' => 'Plano básico ideal para pequenas empresas',
                'price' => 29.90,
                'budget_limit' => 50,
                'features' => json_encode([
                    'Até 50 orçamentos por mês',
                    'Suporte por email',
                    'Relatórios básicos',
                    'Backup automático'
                ]),
                'is_active' => true,
            ],
            [
                'name' => 'Prata',
                'description' => 'Plano intermediário para empresas em crescimento',
                'price' => 59.90,
                'budget_limit' => 150,
                'features' => json_encode([
                    'Até 150 orçamentos por mês',
                    'Suporte prioritário',
                    'Relatórios avançados',
                    'Backup automático',
                    'Integração com APIs',
                    'Personalização de templates'
                ]),
                'is_active' => true,
            ],
            [
                'name' => 'Ouro',
                'description' => 'Plano premium para grandes empresas',
                'price' => 99.90,
                'budget_limit' => null, // Ilimitado
                'features' => json_encode([
                    'Orçamentos ilimitados',
                    'Suporte 24/7',
                    'Relatórios personalizados',
                    'Backup automático',
                    'Integração completa com APIs',
                    'Personalização total',
                    'Gerente de conta dedicado',
                    'Treinamento personalizado'
                ]),
                'is_active' => true,
            ],
        ];

        foreach ($plans as $planData) {
            Plan::firstOrCreate(
                ['name' => $planData['name']],
                $planData
            );
        }
    }
}
