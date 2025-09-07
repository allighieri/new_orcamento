<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\BankAccount;
use App\Models\Company;
use App\Models\Compe;
use Faker\Factory as Faker;

class BankAccountSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faker = Faker::create('pt_BR');
        
        // Verificar se existem empresas
        $companies = Company::all();
        if ($companies->isEmpty()) {
            $this->command->warn('Nenhuma empresa encontrada. Execute o CompanySeeder primeiro.');
            return;
        }
        
        // Verificar se existem bancos
        $bancos = Compe::all();
        if ($bancos->isEmpty()) {
            $this->command->warn('Nenhum banco encontrado. Execute o CompeSeeder primeiro.');
            return;
        }
        
        foreach ($companies as $company) {
            // Criar 2-3 contas bancárias por empresa
            $numContas = $faker->numberBetween(2, 3);
            
            for ($i = 0; $i < $numContas; $i++) {
                $banco = $bancos->random();
                $tipoConta = $faker->randomElement(['Conta', 'PIX']);
                
                $dadosConta = [
                    'company_id' => $company->id,
                    'compe_id' => $banco->id,
                    'type' => $tipoConta,
                    'active' => true,
                ];
                
                if ($tipoConta === 'Conta') {
                    // Conta corrente
                    $agencia = $faker->numerify('####');
                    $conta = $faker->numerify('#####-#');
                    
                    $dadosConta = array_merge($dadosConta, [
                        'branch' => $agencia,
                        'account' => $conta,
                        'description' => "Conta Corrente {$banco->bank_name} - {$company->fantasy_name}",
                        'key' => null,
                        'key_desc' => null,
                    ]);
                } else {
                    // PIX
                    $tipoChave = $faker->randomElement(['CPF', 'CNPJ', 'email', 'telefone']);
                    
                    switch ($tipoChave) {
                        case 'CPF':
                            $chaveDesc = $faker->cpf(false);
                            break;
                        case 'CNPJ':
                            $chaveDesc = $faker->cnpj(false);
                            break;
                        case 'email':
                            $chaveDesc = strtolower($faker->firstName()) . '@' . strtolower($company->fantasy_name) . '.com.br';
                            break;
                        case 'telefone':
                            $chaveDesc = $faker->cellphone(false);
                            break;
                    }
                    
                    $dadosConta = array_merge($dadosConta, [
                        'branch' => null,
                        'account' => null,
                        'key' => $tipoChave,
                        'key_desc' => $chaveDesc,
                        'description' => "PIX {$tipoChave} {$banco->bank_name} - {$company->fantasy_name}",
                    ]);
                }
                
                BankAccount::create($dadosConta);
            }
        }
        
        $this->command->info('Contas bancárias criadas com sucesso!');
    }
}