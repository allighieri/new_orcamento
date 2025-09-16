<?php

namespace Database\Seeders;

use App\Models\Client;
use Illuminate\Database\Seeder;
use Faker\Factory as Faker;

class ClientSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faker = Faker::create('pt_BR');
        $companies = \App\Models\Company::all();
        
        if ($companies->isEmpty()) {
            $this->command->warn('Nenhuma empresa encontrada. Execute CompanySeeder primeiro.');
            return;
        }
        
        foreach ($companies as $index => $company) {
            if ($index === 0) {
                // Clientes para Construtora Alvorada
                $constructionClients = [
                    [
                        'fantasy_name' => 'RENAULT SIA',
                        'corporate_name' => 'RENAULT SIA LTDA',
                        'document_number' => '975.026.851-20',
                        'state_registration' => '152345678',
                        'phone' => '(61) 99253-0902',
                        'email' => 'agenciaolhardigital@gmail.com',
                        'address_line_2' => 'CONJ A LOJA 23',
                        'address' => 'QMSW 2',
                        'district' => 'Sudoeste',
                        'city' => 'São Paulo',
                        'state' => 'SP',
                        'cep' => '70.680-200'
                    ]
                ];
                
                foreach ($constructionClients as $clientData) {
                    $clientData['company_id'] = $company->id;
                    Client::create($clientData);
                }
            } else {
                // Clientes para TechnoInfo
                $techClients = [
                    [
                        'fantasy_name' => 'Escola Digital Futuro',
                        'corporate_name' => 'Instituto Educacional Futuro Ltda',
                        'document_number' => '67.890.123/0001-56',
                        'state_registration' => '678901235',
                        'phone' => '(21) 3456-7890',
                        'email' => 'ti@escolafuturo.edu.br',
                        'address' => 'Rua da Educação, 456',
                        'city' => 'Rio de Janeiro',
                        'state' => 'RJ'
                    ],
                    [
                        'fantasy_name' => 'Clínica Médica Saúde Total',
                        'corporate_name' => 'Centro Médico Saúde Total S.A.',
                        'document_number' => '78.901.234/0001-67',
                        'state_registration' => '789012346',
                        'phone' => '(21) 2345-6789',
                        'email' => 'informatica@saudetotal.med.br',
                        'address' => 'Av. das Américas, 1200',
                        'city' => 'Rio de Janeiro',
                        'state' => 'RJ'
                    ],
                    [
                        'fantasy_name' => 'Escritório Advocacia & Cia',
                        'corporate_name' => 'Advocacia & Cia Sociedade de Advogados',
                        'document_number' => '89.012.345/0001-78',
                        'state_registration' => '890123457',
                        'phone' => '(21) 1234-5678',
                        'email' => 'suporte@advocaciaecia.adv.br',
                        'address' => 'Rua do Ouvidor, 85',
                        'city' => 'Rio de Janeiro',
                        'state' => 'RJ'
                    ],
                    [
                        'fantasy_name' => 'Restaurante Sabor Carioca',
                        'corporate_name' => 'Gastronomia Carioca Ltda',
                        'document_number' => '90.123.456/0001-89',
                        'state_registration' => '901234568',
                        'phone' => '(21) 9876-5432',
                        'email' => 'gerencia@saborcarioca.com.br',
                        'address' => 'Av. Atlântica, 2800',
                        'city' => 'Rio de Janeiro',
                        'state' => 'RJ'
                    ],
                    [
                        'fantasy_name' => 'Loja de Roupas Moda Rio',
                        'corporate_name' => 'Confecções Moda Rio ME',
                        'document_number' => '01.234.567/0001-90',
                        'state_registration' => '012345679',
                        'phone' => '(21) 8765-4321',
                        'email' => 'vendas@modario.com.br',
                        'address' => 'Rua Visconde de Pirajá, 550',
                        'city' => 'Rio de Janeiro',
                        'state' => 'RJ'
                    ]
                ];
                
                foreach ($techClients as $clientData) {
                    $clientData['company_id'] = $company->id;
                    Client::create($clientData);
                }
            }
        }
    }
}