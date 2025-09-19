<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Payment;

echo "=== VERIFICAÇÃO DE PAGAMENTOS ===\n";
echo "Pagamentos recentes:\n";

$payments = Payment::orderBy('created_at', 'desc')->limit(5)->get(['id', 'status', 'asaas_payment_id', 'created_at']);

foreach ($payments as $payment) {
    echo "ID: {$payment->id} | Status: {$payment->status} | Asaas ID: {$payment->asaas_payment_id} | Criado: {$payment->created_at}\n";
}

echo "\n=== CONTADORES ===\n";
echo "Total de pagamentos: " . Payment::count() . "\n";
echo "Pagamentos pending: " . Payment::where('status', 'pending')->count() . "\n";
echo "Pagamentos paid: " . Payment::where('status', 'paid')->count() . "\n";