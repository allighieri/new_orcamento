<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

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
        
        // Criar dados relacionados às empresas
        $this->call([
            CategorySeeder::class,
            ProductSeeder::class,
            ClientSeeder::class,
        ]);
    }
    
    private function createTestUsers(): void
    {
        $companies = \App\Models\Company::all();
        
        if ($companies->isNotEmpty()) {
            // Criar usuário admin para a primeira empresa
            \App\Models\User::factory()->create([
                'name' => 'Admin Empresa 1',
                'email' => 'admin@empresa1.com',
                'role' => 'admin',
                'company_id' => $companies->first()->id,
            ]);
            
            // Criar usuário comum para a primeira empresa
            \App\Models\User::factory()->create([
                'name' => 'Usuário Empresa 1',
                'email' => 'user@empresa1.com',
                'role' => 'user',
                'company_id' => $companies->first()->id,
            ]);
            
            // Se houver segunda empresa, criar usuários para ela também
            if ($companies->count() > 1) {
                \App\Models\User::factory()->create([
                    'name' => 'Admin Empresa 2',
                    'email' => 'admin@empresa2.com',
                    'role' => 'admin',
                    'company_id' => $companies->skip(1)->first()->id,
                ]);
                
                \App\Models\User::factory()->create([
                    'name' => 'Usuário Empresa 2',
                    'email' => 'user@empresa2.com',
                    'role' => 'user',
                    'company_id' => $companies->skip(1)->first()->id,
                ]);
            }
        }
    }
}
