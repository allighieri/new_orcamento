<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\PaymentMethod;
use App\Models\Company;

class CompanyPaymentMethodSeeder extends Seeder
{
    /**
     * Seed métodos de pagamento específicos para empresas.
     */
    public function run(): void
    {
        // Buscar a primeira empresa para exemplo
        $company = Company::first();
        
        if (!$company) {
            $this->command->info('Nenhuma empresa encontrada. Criando métodos globais apenas.');
            return;
        }

        // Métodos de pagamento específicos da empresa
        $companyPaymentMethods = [
            [
                'company_id' => $company->id,
                'name' => 'Crediário da Casa',
                'slug' => 'crediario-casa',
                'allows_installments' => true,
                'max_installments' => 24,
                'is_active' => true,
            ],
            [
                'company_id' => $company->id,
                'name' => 'Cartão Fidelidade',
                'slug' => 'cartao-fidelidade',
                'allows_installments' => true,
                'max_installments' => 6,
                'is_active' => true,
            ],
            [
                'company_id' => $company->id,
                'name' => 'Desconto à Vista',
                'slug' => 'desconto-vista',
                'allows_installments' => false,
                'max_installments' => 1,
                'is_active' => true,
            ],
        ];

        foreach ($companyPaymentMethods as $method) {
            PaymentMethod::updateOrCreate(
                [
                    'company_id' => $method['company_id'],
                    'slug' => $method['slug']
                ],
                $method
            );
        }

        $this->command->info('Métodos de pagamento específicos da empresa "' . $company->name . '" criados com sucesso!');
        
        // Exemplo de como uma empresa pode personalizar um método global
        // Criando uma versão customizada do PIX para a empresa
        PaymentMethod::updateOrCreate(
            [
                'company_id' => $company->id,
                'slug' => 'pix-promocional'
            ],
            [
                'company_id' => $company->id,
                'name' => 'PIX com Desconto',
                'slug' => 'pix-promocional',
                'allows_installments' => false,
                'max_installments' => 1,
                'is_active' => true,
            ]
        );
        
        $this->command->info('Método PIX promocional criado para a empresa!');
    }
}