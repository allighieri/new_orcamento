<?php

namespace Database\Seeders;

use App\Models\Company;
use Illuminate\Database\Seeder;

class CompanySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $companies = [
            [
                'fantasy_name' => 'Minha Empresa',
                'corporate_name' => 'Minha Empresa Ltda',
                'document_number' => '12.345.678/0001-90',
                'state_registration' => '123456789',
                'phone' => '(11) 99999-9999',
                'email' => 'contato@minhaempresa.com.br',
                'address' => 'Rua Principal, 123',
                'city' => 'São Paulo',
                'state' => 'SP'
            ],
            [
                'fantasy_name' => 'Tech Corp',
                'corporate_name' => 'Tech Corporation S.A.',
                'document_number' => '98.765.432/0001-10',
                'state_registration' => '987654321',
                'phone' => '(21) 88888-8888',
                'email' => 'info@techcorp.com.br',
                'address' => 'Av. Tecnologia, 456',
                'city' => 'Rio de Janeiro',
                'state' => 'RJ'
            ]
        ];

        foreach ($companies as $companyData) {
            Company::create($companyData);
        }
    }
}