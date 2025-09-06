<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\PaymentOptionMethod;
use Illuminate\Support\Facades\DB;

class PaymentOptionMethodSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {

        DB::table('payment_option_methods')->truncate();

        $paymentOptionMethods = [
            [
                'method' => 'Boleto',
                'description' => 'Pagamento via boleto bancário.',
                'active' => 1
            ],
            [
                'method' => 'PIX',
                'description' => 'Pagamento instantâneo 24/7.',
                'active' => 1
            ],
            [
                'method' => 'PIX por Aproximação',
                'description' => 'Permite pagamentos instantâneos apenas aproximando o celular ou outro dispositivo.',
                'active' => 1
            ],
            [
                'method' => 'PIX automático',
                'description' => 'Para pagamentos recorrentes (contas de luz, água, assinaturas), funcionando de forma semelhante ao débito automático.',
                'active' => 1
            ],
            [
                'method' => 'Pix Parcelado',
                'description' => 'Permite parcelar compras utilizando o PIX, com o lojista recebendo o valor total à vista.',
                'active' => 1
            ],
            [
                'method' => 'Cartão de Crédito',
                'description' => 'Permite compras parceladas.',
                'active' => 1
            ],
            [
                'method' => 'Cartão de Débito',
                'description' => 'Permite compras a vista.',
                'active' => 1
            ],
            [
                'method' => 'Cartão NFC',
                'description' => 'Permite compras por aproximação com cartão de Crédito e Débito.',
                'active' => 1
            ],
            [
                'method' => 'Transferência Bancária TED',
                'description' => 'Para transferências de valores mais altos entre contas de diferentes bancos, compensa até o final do dia útil.',
                'active' => 1
            ],
            [
                'method' => 'Transferência Bancária DOC',
                'description' => 'Para transferências de valores mais altos entre contas de diferentes bancos, compensa até o final do próximo dia útil.',
                'active' => 1
            ],
            [
                'method' => 'Promissória',
                'description' => 'Pagamento via promissória.',
                'active' => 1
            ]
        ];

        foreach ($paymentOptionMethods as $method) {
            PaymentOptionMethod::updateOrCreate(
                ['method' => $method['method']],
                $method
            );
        }

        $this->command->info('Métodos de opção de pagamento criados com sucesso!');
    }
}
