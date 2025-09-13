<?php

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Payment;
use App\Models\Subscription;

echo "=== VERIFICAÇÃO DE PAGAMENTOS ===\n\n";

// Verificar pagamentos recentes
$payments = Payment::orderBy('created_at', 'desc')->take(5)->get();
echo "Últimos 5 pagamentos:\n";
foreach ($payments as $payment) {
    echo "ID: {$payment->id} | Asaas Payment: {$payment->asaas_payment_id} | Asaas Subscription: {$payment->asaas_subscription_id} | Status: {$payment->status} | Amount: {$payment->amount}\n";
}

echo "\n=== BUSCANDO PAGAMENTO ESPECÍFICO ===\n";

// Buscar por subscription_id
$payment = Payment::where('asaas_subscription_id', 'sub_i16k0c2drglhqtyj')->first();
if ($payment) {
    echo "✅ Pagamento encontrado por subscription_id:\n";
    echo "ID: {$payment->id}\n";
    echo "Company ID: {$payment->company_id}\n";
    echo "Plan ID: {$payment->plan_id}\n";
    echo "Status: {$payment->status}\n";
    echo "Amount: {$payment->amount}\n";
    echo "Billing Cycle: {$payment->billing_cycle}\n";
    echo "Asaas Payment ID: {$payment->asaas_payment_id}\n";
    echo "Asaas Subscription ID: {$payment->asaas_subscription_id}\n";
} else {
    echo "❌ Pagamento não encontrado por subscription_id\n";
}

echo "\n=== VERIFICANDO ASSINATURAS ===\n";

// Verificar assinaturas
$subscriptions = Subscription::orderBy('created_at', 'desc')->take(3)->get();
echo "Últimas 3 assinaturas:\n";
foreach ($subscriptions as $sub) {
    echo "ID: {$sub->id} | Company: {$sub->company_id} | Plan: {$sub->plan_id} | Status: {$sub->status} | Asaas Sub ID: {$sub->asaas_subscription_id}\n";
}

echo "\n=== FIM DA VERIFICAÇÃO ===\n";