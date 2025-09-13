<?php

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "=== VERIFICANDO PAGAMENTO CRIADO ===\n\n";

$payment = App\Models\Payment::find(7);

if ($payment) {
    echo "ID: {$payment->id}\n";
    echo "Company ID: {$payment->company_id}\n";
    echo "Plan ID: " . ($payment->plan_id ?? 'NULL') . "\n";
    echo "Type: {$payment->type}\n";
    echo "Status: {$payment->status}\n";
    echo "Amount: R$ {$payment->amount}\n";
    echo "Asaas Payment ID: {$payment->asaas_payment_id}\n";
    echo "Extra Budgets Quantity: {$payment->extra_budgets_quantity}\n";
    echo "Description: {$payment->description}\n";
    echo "Due Date: {$payment->due_date}\n";
    echo "Created At: {$payment->created_at}\n";
} else {
    echo "Pagamento não encontrado!\n";
}

echo "\n=== TODOS OS PAGAMENTOS ===\n\n";

$payments = App\Models\Payment::orderBy('id', 'desc')->take(5)->get();

foreach ($payments as $payment) {
    echo "ID: {$payment->id} | Type: {$payment->type} | Plan ID: " . ($payment->plan_id ?? 'NULL') . " | Status: {$payment->status} | Amount: R$ {$payment->amount}\n";
}