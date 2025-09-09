<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\PaymentMethod;
use App\Models\PaymentOptionMethod;

class PaymentMethodSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Buscar os métodos de opção de pagamento disponíveis
        $pixMethod = PaymentOptionMethod::where('method', 'PIX')->first();
        $creditCardMethod = PaymentOptionMethod::where('method', 'Cartão de Crédito')->first();
        $debitCardMethod = PaymentOptionMethod::where('method', 'Cartão de Débito')->first();
        $tedMethod = PaymentOptionMethod::where('method', 'Transferência Bancária TED')->first();
        $boletoMethod = PaymentOptionMethod::where('method', 'Boleto')->first();
        $promissoryMethod = PaymentOptionMethod::where('method', 'Promissória')->first();

        // Métodos de pagamento globais (disponíveis para todas as empresas)
        $paymentMethods = [
            [
                'company_id' => null, // Global
                'payment_option_method_id' => $pixMethod?->id,
                'slug' => 'pix',
                'allows_installments' => false,
                'max_installments' => 1,
                'is_active' => true,
            ],
            [
                'company_id' => null, // Global
                'payment_option_method_id' => $pixMethod?->id, // Usar PIX para dinheiro
                'slug' => 'dinheiro',
                'allows_installments' => false,
                'max_installments' => 1,
                'is_active' => true,
            ],
            [
                'company_id' => null, // Global
                'payment_option_method_id' => $creditCardMethod?->id,
                'slug' => 'cartao-credito',
                'allows_installments' => true,
                'max_installments' => 12,
                'is_active' => true,
            ],
            [
                'company_id' => null, // Global
                'payment_option_method_id' => $debitCardMethod?->id,
                'slug' => 'cartao-debito',
                'allows_installments' => false,
                'max_installments' => 1,
                'is_active' => true,
            ],
            [
                'company_id' => null, // Global
                'payment_option_method_id' => $tedMethod?->id,
                'slug' => 'transferencia-bancaria',
                'allows_installments' => false,
                'max_installments' => 1,
                'is_active' => true,
            ],
            [
                'company_id' => null, // Global
                'payment_option_method_id' => $boletoMethod?->id,
                'slug' => 'boleto',
                'allows_installments' => true,
                'max_installments' => 6,
                'is_active' => true,
            ],
            [
                'company_id' => null, // Global
                'payment_option_method_id' => $promissoryMethod?->id,
                'slug' => 'cheque',
                'allows_installments' => true,
                'max_installments' => 3,
                'is_active' => true,
            ],
            [
                'company_id' => null, // Global
                'payment_option_method_id' => $pixMethod?->id, // Usar PIX para outros
                'slug' => 'outros',
                'allows_installments' => false,
                'max_installments' => 1,
                'is_active' => true,
            ],
        ];

        // Filtrar apenas métodos com payment_option_method_id válido
        $paymentMethods = array_filter($paymentMethods, function($method) {
            return $method['payment_option_method_id'] !== null;
        });

        foreach ($paymentMethods as $method) {
            PaymentMethod::updateOrCreate(
                ['slug' => $method['slug']],
                $method
            );
        }
    }
}