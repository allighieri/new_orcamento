<?php

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "=== VERIFICANDO USAGE CONTROL ===\n\n";

$usageControl = App\Models\UsageControl::where('company_id', 1)
    ->where('year', date('Y'))
    ->where('month', date('n'))
    ->first();

if ($usageControl) {
    echo "Company ID: {$usageControl->company_id}\n";
    echo "Year: {$usageControl->year}\n";
    echo "Month: {$usageControl->month}\n";
    echo "Budgets Limit: {$usageControl->budgets_limit}\n";
    echo "Budgets Used: {$usageControl->budgets_used}\n";
    echo "Extra Budgets Purchased: {$usageControl->extra_budgets_purchased}\n";
    echo "Extra Amount Paid: R$ {$usageControl->extra_amount_paid}\n";
    echo "Total Available: " . ($usageControl->budgets_limit + $usageControl->extra_budgets_purchased) . "\n";
    echo "Remaining: " . ($usageControl->budgets_limit + $usageControl->extra_budgets_purchased - $usageControl->budgets_used) . "\n";
} else {
    echo "Usage control não encontrado para o mês atual\n";
    
    // Verificar se existe algum usage control para esta empresa
    $allUsageControls = App\Models\UsageControl::where('company_id', 1)->get();
    
    if ($allUsageControls->count() > 0) {
        echo "\n=== TODOS OS USAGE CONTROLS DA EMPRESA ===\n";
        foreach ($allUsageControls as $uc) {
            echo "Year: {$uc->year}, Month: {$uc->month}, Extra Budgets: {$uc->extra_budgets_purchased}, Extra Amount: R$ {$uc->extra_amount_paid}\n";
        }
    } else {
        echo "Nenhum usage control encontrado para esta empresa\n";
    }
}

echo "\n=== VERIFICANDO PAGAMENTOS RECEBIDOS ===\n";

$receivedPayments = App\Models\Payment::where('company_id', 1)
    ->where('type', 'extra_budgets')
    ->where('status', 'RECEIVED')
    ->get();

foreach ($receivedPayments as $payment) {
    echo "Payment ID: {$payment->id}, Status: {$payment->status}, Quantity: {$payment->extra_budgets_quantity}, Amount: R$ {$payment->amount}\n";
}