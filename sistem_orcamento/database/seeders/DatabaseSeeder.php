<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Faker\Factory as Faker;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Criar super admin primeiro
        $this->call([
            SuperAdminSeeder::class,
        ]);
        
        // Criar empresas
        $this->call([
            CompanySeeder::class,
        ]);
        
        // Criar usuários de teste vinculados às empresas
        $this->createTestUsers();
        
        // Chamar outros seeders na ordem correta
        $this->call([
            // Seeders básicos do sistema
            CompeSeeder::class,
            SystemSettingSeeder::class,
            PlansSeeder::class,
            
            // Seeders de categorias e produtos
            CategorySeeder::class,
            ProductSeeder::class,
            
            // Seeders de clientes e contatos
            ClientSeeder::class,
            ContactSeeder::class,
            
            // Seeders de métodos de pagamento
            PaymentOptionMethodSeeder::class,
            PaymentMethodSeeder::class,
            CompanyPaymentMethodSeeder::class,
            
            // Seeders de contas bancárias
            BankAccountSeeder::class,
            
            // Seeders de orçamentos
            BudgetSeeder::class,
            BudgetBankAccountSeeder::class,
            
            // Seeders de exemplos de pagamento
            PaymentExampleSeeder::class,
        ]);
    }
    
    private function createTestUsers(): void
    {
        $faker = Faker::create('pt_BR');
        $companies = \App\Models\Company::all();
        
        if ($companies->isNotEmpty()) {
            // Criar usuário admin para a primeira empresa (Construtora)
            \App\Models\User::factory()->create([
                'name' => 'Carlos Eduardo Silva',
                'email' => 'carlos.silva@construtoralvorada.com.br',
                'role' => 'admin',
                'company_id' => $companies->first()->id,
            ]);
            
            // Criar usuário comum para a primeira empresa
            \App\Models\User::factory()->create([
                'name' => 'Ana Paula Santos',
                'email' => 'ana.santos@construtoralvorada.com.br',
                'role' => 'user',
                'company_id' => $companies->first()->id,
            ]);
            
            // Se houver segunda empresa, criar usuários para ela também
            if ($companies->count() > 1) {
                \App\Models\User::factory()->create([
                    'name' => 'Roberto Oliveira',
                    'email' => 'roberto.oliveira@technoinfo.com.br',
                    'role' => 'admin',
                    'company_id' => $companies->skip(1)->first()->id,
                ]);
                
                \App\Models\User::factory()->create([
                    'name' => 'Mariana Costa',
                    'email' => 'mariana.costa@technoinfo.com.br',
                    'role' => 'user',
                    'company_id' => $companies->skip(1)->first()->id,
                ]);
            }
        }
    }
}
