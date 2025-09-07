<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\PaymentMethod;
use App\Models\Company;
use App\Models\PaymentOptionMethod;

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

        // Buscar métodos de opção de pagamento disponíveis
        $pixMethod = PaymentOptionMethod::where('method', 'PIX')->first();
        $promissoryMethod = PaymentOptionMethod::where('method', 'Promissória')->first();
        $creditCardMethod = PaymentOptionMethod::where('method', 'Cartão de Crédito')->first();

        // Métodos de pagamento específicos da empresa
        $companyPaymentMethods = [
            [
                'company_id' => $company->id,
                'payment_option_method_id' => $promissoryMethod?->id ?? $pixMethod?->id,
                'slug' => 'crediario-casa',
                'allows_installments' => true,
                'max_installments' => 24,
                'is_active' => true,
            ],
            [
                'company_id' => $company->id,
                'payment_option_method_id' => $creditCardMethod?->id ?? $pixMethod?->id,
                'slug' => 'cartao-fidelidade',
                'allows_installments' => true,
                'max_installments' => 6,
                'is_active' => true,
            ],
            [
                'company_id' => $company->id,
                'payment_option_method_id' => $pixMethod?->id,
                'slug' => 'desconto-vista',
                'allows_installments' => false,
                'max_installments' => 1,
                'is_active' => true,
            ],
        ];

        // Filtrar apenas métodos com payment_option_method_id válido
        $companyPaymentMethods = array_filter($companyPaymentMethods, function($method) {
            return $method['payment_option_method_id'] !== null;
        });

        foreach ($companyPaymentMethods as $method) {
            PaymentMethod::updateOrCreate(
                [
                    'company_id' => $method['company_id'],
                    'slug' => $method['slug']
                ],
                $method
            );
        }

        $this->command->info('Métodos de pagamento específicos da empresa "' . $company->fantasy_name . '" criados com sucesso!');
        
        // Exemplo de como uma empresa pode personalizar um método global
        // Criando uma versão customizada do PIX para a empresa
        if ($pixMethod) {
            PaymentMethod::updateOrCreate(
                [
                    'company_id' => $company->id,
                    'slug' => 'pix-promocional'
                ],
                [
                    'company_id' => $company->id,
                    'payment_option_method_id' => $pixMethod->id,
                    'slug' => 'pix-promocional',
                    'allows_installments' => false,
                    'max_installments' => 1,
                    'is_active' => true,
                ]
            );
            
            $this->command->info('Método PIX promocional criado para a empresa!');
        }
    }
}