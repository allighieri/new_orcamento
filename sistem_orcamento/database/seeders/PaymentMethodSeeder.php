<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\PaymentMethod;

class PaymentMethodSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Métodos de pagamento globais (disponíveis para todas as empresas)
        $paymentMethods = [
            [
                'company_id' => null, // Global
                'name' => 'PIX',
                'slug' => 'pix',
                'allows_installments' => false,
                'max_installments' => 1,
                'is_active' => true,
            ],
            [
                'company_id' => null, // Global
                'name' => 'Dinheiro',
                'slug' => 'dinheiro',
                'allows_installments' => false,
                'max_installments' => 1,
                'is_active' => true,
            ],
            [
                'company_id' => null, // Global
                'name' => 'Cartão de Crédito',
                'slug' => 'cartao-credito',
                'allows_installments' => true,
                'max_installments' => 12,
                'is_active' => true,
            ],
            [
                'company_id' => null, // Global
                'name' => 'Cartão de Débito',
                'slug' => 'cartao-debito',
                'allows_installments' => false,
                'max_installments' => 1,
                'is_active' => true,
            ],
            [
                'company_id' => null, // Global
                'name' => 'Transferência Bancária',
                'slug' => 'transferencia-bancaria',
                'allows_installments' => false,
                'max_installments' => 1,
                'is_active' => true,
            ],
            [
                'company_id' => null, // Global
                'name' => 'Boleto',
                'slug' => 'boleto',
                'allows_installments' => true,
                'max_installments' => 6,
                'is_active' => true,
            ],
            [
                'company_id' => null, // Global
                'name' => 'Cheque',
                'slug' => 'cheque',
                'allows_installments' => true,
                'max_installments' => 3,
                'is_active' => true,
            ],
            [
                'company_id' => null, // Global
                'name' => 'Outros',
                'slug' => 'outros',
                'allows_installments' => false,
                'max_installments' => 1,
                'is_active' => true,
            ],
        ];

        foreach ($paymentMethods as $method) {
            PaymentMethod::updateOrCreate(
                ['slug' => $method['slug']],
                $method
            );
        }
    }
}