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
                'description' => json_encode([
                    '5 orçamentos por mês',
                    'Suporte por email',
                    'Envio de e-mails integrado com Gmail',
                    'Templates de Email personalizados'
                ]),
                'budget_limit' => 5,
                'monthly_price' => 30.00,
                'yearly_price' => 300.00,
                'active' => true,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'name' => 'Prata',
                'slug' => 'prata',
                'description' => json_encode([
                    '50 orçamentos por mês',
                    'Suporte por email',
                    'Envio de e-mails integrado com Gmail',
                    'Templates de Email personalizados'
                ]),
                'budget_limit' => 50,
                'monthly_price' => 40.00,
                'yearly_price' => 420.00,
                'active' => true,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'name' => 'Ouro',
                'slug' => 'ouro',
                'description' => json_encode([
                    'Orçamentos ilimitados',
                    'Suporte por email',
                    'Envio de e-mails integrado com Gmail',
                    'Templates de Email personalizados'
                ]),
                'budget_limit' => null, // Ilimitado
                'monthly_price' => 50.00,
                'yearly_price' => 540.00,
                'active' => true,
                'created_at' => now(),
                'updated_at' => now()
            ]
        ];
        
        DB::table('plans')->insert($plans);
        
        $this->command->info('Planos Bronze, Prata e Ouro criados com sucesso!');
    }
}
