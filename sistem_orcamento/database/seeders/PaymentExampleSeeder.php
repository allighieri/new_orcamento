<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Budget;
use App\Models\BudgetPayment;
use App\Models\PaymentMethod;
use App\Models\PaymentInstallment;
use Carbon\Carbon;

class PaymentExampleSeeder extends Seeder
{
    /**
     * Exemplos de como usar o sistema de pagamentos
     * Baseado nos cenários fornecidos pelo usuário
     */
    public function run(): void
    {
        // Buscar métodos de pagamento disponíveis
        $pix = PaymentMethod::where('slug', 'pix')->first();
        $creditCard = PaymentMethod::where('slug', 'cartao-credito')->first();
        $cash = PaymentMethod::where('slug', 'dinheiro')->first();
        $bankSlip = PaymentMethod::where('slug', 'boleto')->first();
        $debitCard = PaymentMethod::where('slug', 'cartao-debito')->first();
        
        // Se não encontrar métodos específicos, usar os disponíveis
        if (!$pix) $pix = PaymentMethod::first();
        if (!$creditCard) $creditCard = PaymentMethod::skip(1)->first() ?: PaymentMethod::first();
        if (!$cash) $cash = PaymentMethod::first();
        if (!$bankSlip) $bankSlip = PaymentMethod::first();
        if (!$debitCard) $debitCard = PaymentMethod::first();

        // Exemplo 1: PIX na retirada (R$ 1.500) + Cartão de Crédito 3x (R$ 1.500)
        $this->createExamplePayment1($pix, $creditCard);

        // Exemplo 2: Dinheiro na aprovação (R$ 1.000) + PIX na retirada (R$ 1.000) + Boleto 60 dias (R$ 1.000)
        $this->createExamplePayment2($cash, $pix, $bankSlip);

        // Exemplo 3: Cartão de Débito à vista na retirada (R$ 3.000)
        $this->createExamplePayment3($debitCard);

        // Exemplo 4: Cartão de Crédito 3x na aprovação (R$ 1.500) + Cartão de Crédito 2x na retirada (R$ 1.500)
        $this->createExamplePayment4($creditCard);

        // Exemplo 5: PIX na aprovação (R$ 500) + PIX na retirada (R$ 1.500) + Boleto 30 dias (R$ 1.000)
        $this->createExamplePayment5($pix, $bankSlip);
    }

    private function createExamplePayment1($pix, $creditCard)
    {
        // Buscar um orçamento existente ou criar um exemplo
        $budget = Budget::first();
        if (!$budget) return;

        // PIX na retirada - R$ 1.500
        $pixPayment = BudgetPayment::create([
            'budget_id' => $budget->id,
            'payment_method_id' => $pix->id,
            'amount' => 1500.00,
            'installments' => 1,
            'payment_moment' => 'pickup',
            'notes' => 'PIX na retirada do produto',
            'order' => 1
        ]);

        // Cartão de Crédito 3x - R$ 1.500
        $creditPayment = BudgetPayment::create([
            'budget_id' => $budget->id,
            'payment_method_id' => $creditCard->id,
            'amount' => 1500.00,
            'installments' => 3,
            'payment_moment' => 'pickup',
            'notes' => 'Cartão de crédito parcelado em 3x',
            'order' => 2
        ]);

        // Criar parcelas do cartão de crédito
        for ($i = 1; $i <= 3; $i++) {
            PaymentInstallment::create([
                'budget_payment_id' => $creditPayment->id,
                'installment_number' => $i,
                'amount' => 500.00,
                'due_date' => now()->addMonths($i - 1),
                'status' => 'pending'
            ]);
        }
    }

    private function createExamplePayment2($cash, $pix, $bankSlip)
    {
        $budget = Budget::skip(1)->first();
        if (!$budget) return;

        // Dinheiro na aprovação - R$ 1.000
        BudgetPayment::create([
            'budget_id' => $budget->id,
            'payment_method_id' => $cash->id,
            'amount' => 1000.00,
            'installments' => 1,
            'payment_moment' => 'approval',
            'notes' => 'Entrada em dinheiro na aprovação',
            'order' => 1
        ]);

        // PIX na retirada - R$ 1.000
        BudgetPayment::create([
            'budget_id' => $budget->id,
            'payment_method_id' => $pix->id,
            'amount' => 1000.00,
            'installments' => 1,
            'payment_moment' => 'pickup',
            'notes' => 'PIX na retirada do produto',
            'order' => 2
        ]);

        // Boleto para 60 dias - R$ 1.000
        $bankSlipPayment = BudgetPayment::create([
            'budget_id' => $budget->id,
            'payment_method_id' => $bankSlip->id,
            'amount' => 1000.00,
            'installments' => 1,
            'payment_moment' => 'pickup',
            'days_after_pickup' => 60,
            'notes' => 'Boleto com vencimento 60 dias após retirada',
            'order' => 3
        ]);

        // Criar parcela do boleto
        PaymentInstallment::create([
            'budget_payment_id' => $bankSlipPayment->id,
            'installment_number' => 1,
            'amount' => 1000.00,
            'due_date' => now()->addDays(60),
            'status' => 'pending'
        ]);
    }

    private function createExamplePayment3($debitCard)
    {
        $budget = Budget::skip(2)->first();
        if (!$budget) return;

        // Cartão de Débito à vista - R$ 3.000
        BudgetPayment::create([
            'budget_id' => $budget->id,
            'payment_method_id' => $debitCard->id,
            'amount' => 3000.00,
            'installments' => 1,
            'payment_moment' => 'pickup',
            'notes' => 'Cartão de débito à vista na retirada',
            'order' => 1
        ]);
    }

    private function createExamplePayment4($creditCard)
    {
        $budget = Budget::skip(3)->first();
        if (!$budget) return;

        // Cartão de Crédito 3x na aprovação - R$ 1.500
        $creditPayment1 = BudgetPayment::create([
            'budget_id' => $budget->id,
            'payment_method_id' => $creditCard->id,
            'amount' => 1500.00,
            'installments' => 3,
            'payment_moment' => 'approval',
            'notes' => 'Cartão de crédito 3x na aprovação',
            'order' => 1
        ]);

        // Cartão de Crédito 2x na retirada - R$ 1.500
        $creditPayment2 = BudgetPayment::create([
            'budget_id' => $budget->id,
            'payment_method_id' => $creditCard->id,
            'amount' => 1500.00,
            'installments' => 2,
            'payment_moment' => 'pickup',
            'notes' => 'Cartão de crédito 2x na retirada',
            'order' => 2
        ]);

        // Criar parcelas do primeiro cartão (3x)
        for ($i = 1; $i <= 3; $i++) {
            PaymentInstallment::create([
                'budget_payment_id' => $creditPayment1->id,
                'installment_number' => $i,
                'amount' => 500.00,
                'due_date' => now()->addMonths($i - 1),
                'status' => 'pending'
            ]);
        }

        // Criar parcelas do segundo cartão (2x)
        for ($i = 1; $i <= 2; $i++) {
            PaymentInstallment::create([
                'budget_payment_id' => $creditPayment2->id,
                'installment_number' => $i,
                'amount' => 750.00,
                'due_date' => now()->addMonths($i - 1),
                'status' => 'pending'
            ]);
        }
    }

    private function createExamplePayment5($pix, $bankSlip)
    {
        $budget = Budget::skip(4)->first();
        if (!$budget) return;

        // PIX na aprovação - R$ 500
        BudgetPayment::create([
            'budget_id' => $budget->id,
            'payment_method_id' => $pix->id,
            'amount' => 500.00,
            'installments' => 1,
            'payment_moment' => 'approval',
            'notes' => 'PIX na aprovação do orçamento',
            'order' => 1
        ]);

        // PIX na retirada - R$ 1.500
        BudgetPayment::create([
            'budget_id' => $budget->id,
            'payment_method_id' => $pix->id,
            'amount' => 1500.00,
            'installments' => 1,
            'payment_moment' => 'pickup',
            'notes' => 'PIX na retirada do produto',
            'order' => 2
        ]);

        // Boleto para 30 dias - R$ 1.000
        $bankSlipPayment = BudgetPayment::create([
            'budget_id' => $budget->id,
            'payment_method_id' => $bankSlip->id,
            'amount' => 1000.00,
            'installments' => 1,
            'payment_moment' => 'pickup',
            'days_after_pickup' => 30,
            'notes' => 'Boleto com vencimento 30 dias após retirada',
            'order' => 3
        ]);

        // Criar parcela do boleto
        PaymentInstallment::create([
            'budget_payment_id' => $bankSlipPayment->id,
            'installment_number' => 1,
            'amount' => 1000.00,
            'due_date' => now()->addDays(30),
            'status' => 'pending'
        ]);
    }
}