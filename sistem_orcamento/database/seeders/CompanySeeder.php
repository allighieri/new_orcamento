<?php

namespace Database\Seeders;

use App\Models\Company;
use Illuminate\Database\Seeder;
use Faker\Factory as Faker;

class CompanySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faker = Faker::create('pt_BR');
        
        // Empresa 1: Construtora
        Company::create([
            'fantasy_name' => 'Construtora Alvorada',
            'corporate_name' => 'Construtora Alvorada Ltda',
            'document_number' => '12.345.678/0001-90',
            'state_registration' => '123456789',
            'phone' => '(11) 3456-7890',
            'email' => 'contato@construtoralvorada.com.br',
            'address' => 'Rua das Construções, 1250',
            'city' => 'São Paulo',
            'state' => 'SP'
        ]);
        
        // Empresa 2: Loja de Informática
        Company::create([
            'fantasy_name' => 'TechnoInfo',
            'corporate_name' => 'TechnoInfo Soluções em Informática Ltda',
            'document_number' => '98.765.432/0001-10',
            'state_registration' => '987654321',
            'phone' => '(21) 2987-6543',
            'email' => 'vendas@technoinfo.com.br',
            'address' => 'Av. Presidente Vargas, 850',
            'city' => 'Rio de Janeiro',
            'state' => 'RJ'
        ]);
    }
}