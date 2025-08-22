<?php

namespace Database\Seeders;

use App\Models\Client;
use Illuminate\Database\Seeder;

class ClientSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $clients = [
            [
                'fantasy_name' => 'Tech Solutions',
                'corporate_name' => 'Tech Solutions Ltda',
                'document_number' => '12.345.678/0001-90',
                'state_registration' => '123456789',
                'phone' => '(11) 99999-9999',
                'email' => 'contato@techsolutions.com.br',
                'address' => 'Rua das Flores, 123',
                'city' => 'São Paulo',
                'state' => 'SP'
            ],
            [
                'fantasy_name' => 'Inovação Digital',
                'corporate_name' => 'Inovação Digital S.A.',
                'document_number' => '98.765.432/0001-10',
                'state_registration' => '987654321',
                'phone' => '(21) 88888-8888',
                'email' => 'info@inovacaodigital.com.br',
                'address' => 'Av. Paulista, 456',
                'city' => 'Rio de Janeiro',
                'state' => 'RJ'
            ],
            [
                'fantasy_name' => 'Consultoria Empresarial',
                'corporate_name' => 'Consultoria Empresarial ME',
                'document_number' => '11.222.333/0001-44',
                'state_registration' => '111222333',
                'phone' => '(31) 77777-7777',
                'email' => 'contato@consultoriaempresarial.com.br',
                'address' => 'Rua dos Negócios, 789',
                'city' => 'Belo Horizonte',
                'state' => 'MG'
            ]
        ];

        foreach ($clients as $clientData) {
            Client::create($clientData);
        }
    }
}