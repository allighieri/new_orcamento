<?php

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "=== SIMULANDO WEBHOOK PARA PAGAMENTO RECEBIDO ===\n\n";

// Buscar o pagamento ID 6 que está como RECEIVED
$payment = App\Models\Payment::find(6);

if (!$payment) {
    echo "Pagamento não encontrado!\n";
    exit(1);
}

echo "Pagamento encontrado:\n";
echo "ID: {$payment->id}\n";
echo "Status: {$payment->status}\n";
echo "Type: {$payment->type}\n";
echo "Extra Budgets Quantity: {$payment->extra_budgets_quantity}\n";
echo "Amount: R$ {$payment->amount}\n\n";

// Simular o processamento do webhook
if ($payment->type === 'extra_budgets' && $payment->status === 'RECEIVED') {
    echo "Processando pagamento de orçamentos extras...\n";
    
    // Buscar assinatura ativa da empresa
    $subscription = App\Models\Subscription::where('company_id', $payment->company_id)
        ->where('status', 'active')
        ->first();
        
    if (!$subscription) {
        echo "❌ Assinatura ativa não encontrada!\n";
        exit(1);
    }
    
    echo "Assinatura ativa encontrada: {$subscription->id}\n";
    echo "Plano: {$subscription->plan->name}\n";
    echo "Budget Limit: {$subscription->plan->budget_limit}\n\n";
    
    // Buscar controle de uso atual
    $usageControl = App\Models\UsageControl::getOrCreateForCurrentMonth(
        $payment->company_id,
        $subscription->id,
        $subscription->plan->budget_limit
    );
    
    echo "Usage Control antes:\n";
    echo "Extra Budgets Purchased: {$usageControl->extra_budgets_purchased}\n";
    echo "Extra Amount Paid: R$ {$usageControl->extra_amount_paid}\n\n";
    
    // Adicionar orçamentos extras (usar a quantidade correta do plano)
    $extraBudgets = $subscription->plan->budget_limit;
    $usageControl->addExtraBudgets($extraBudgets, $payment->amount);
    
    echo "✅ Orçamentos extras adicionados!\n";
    echo "Quantidade adicionada: {$extraBudgets}\n";
    echo "Valor pago: R$ {$payment->amount}\n\n";
    
    // Verificar resultado
    $usageControl->refresh();
    echo "Usage Control depois:\n";
    echo "Extra Budgets Purchased: {$usageControl->extra_budgets_purchased}\n";
    echo "Extra Amount Paid: R$ {$usageControl->extra_amount_paid}\n";
    echo "Total Available: " . ($usageControl->budgets_limit + $usageControl->extra_budgets_purchased) . "\n";
    
} else {
    echo "❌ Pagamento não é de orçamentos extras ou não está como RECEIVED\n";
}

echo "\n=== SIMULAÇÃO CONCLUÍDA ===\n";