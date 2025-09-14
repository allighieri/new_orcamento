<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Plan;
use Illuminate\Support\Facades\DB;

class PlansSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Desabilitar verificações de chave estrangeira temporariamente
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        
        DB::table('plans')->truncate();
        
        // Reabilitar verificações de chave estrangeira
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        
        $plans = [
            [
                'name' => 'Bronze',
                'slug' => 'bronze',
                'description' => 'Plano básico ideal para pequenas empresas. Inclui até 10 orçamentos por mês.',
                'budget_limit' => 10,
                'monthly_price' => 30.00,
                'annual_price' => 25.00,
                'features' => json_encode([
                    'Até 10 orçamentos por mês',
                    'Suporte por email',
                    'Relatórios básicos',
                    'Backup diário'
                ]),
                'active' => true,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'name' => 'Prata',
                'slug' => 'prata',
                'description' => 'Plano intermediário para empresas em crescimento. Inclui até 50 orçamentos por mês.',
                'budget_limit' => 50,
                'monthly_price' => 40.00,
                'annual_price' => 35.00, 
                'features' => json_encode([
                    'Até 50 orçamentos por mês',
                    'Suporte prioritário',
                    'Relatórios avançados',
                    'Backup diário',
                    'Integração com WhatsApp',
                    'Templates personalizados'
                ]),
                'active' => true,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'name' => 'Ouro',
                'slug' => 'ouro',
                'description' => 'Plano premium com orçamentos ilimitados. Ideal para grandes empresas.',
                'budget_limit' => null, // Ilimitado
                'monthly_price' => 50.00,
                'annual_price' => 45.00, 
                'features' => json_encode([
                    'Orçamentos ilimitados',
                    'Suporte 24/7',
                    'Relatórios completos',
                    'Backup em tempo real',
                    'Integração completa WhatsApp',
                    'Templates ilimitados',
                    'API personalizada',
                    'Gerente de conta dedicado'
                ]),
                'active' => true,
                'created_at' => now(),
                'updated_at' => now()
            ]
        ];
        
        DB::table('plans')->insert($plans);
        
        $this->command->info('Planos Bronze, Prata e Ouro criados com sucesso!');
    }
}
