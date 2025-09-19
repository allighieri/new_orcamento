<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Jobs\ProcessAsaasWebhook;
use App\Models\Payment;

echo "=== TESTE DE WEBHOOK ===\n";

// Buscar um pagamento pending para testar
$payment = Payment::where('status', 'pending')->first();

if (!$payment) {
    echo "Nenhum pagamento pending encontrado.\n";
    exit;
}

echo "Testando webhook para pagamento ID: {$payment->id}\n";
echo "Asaas Payment ID: {$payment->asaas_payment_id}\n";
echo "Status atual: {$payment->status}\n";

// Simular payload de webhook PAYMENT_CONFIRMED
$payload = [
    'event' => 'PAYMENT_CONFIRMED',
    'payment' => [
        'id' => $payment->asaas_payment_id,
        'status' => 'CONFIRMED',
        'value' => $payment->amount,
        'customer' => 'cus_000007024162',
        'billingType' => 'PIX',
        'confirmedDate' => date('Y-m-d'),
        'clientPaymentDate' => date('Y-m-d')
    ]
];

echo "\nProcessando webhook...\n";

try {
    $job = new ProcessAsaasWebhook($payload);
    $asaasService = app(\App\Services\AsaasService::class);
    $planUpgradeService = app(\App\Services\PlanUpgradeService::class);
    
    $job->handle($asaasService, $planUpgradeService);
    
    echo "Webhook processado com sucesso!\n";
    
    // Verificar se o status foi atualizado
    $payment->refresh();
    echo "Novo status: {$payment->status}\n";
    
} catch (Exception $e) {
    echo "Erro ao processar webhook: " . $e->getMessage() . "\n";
    echo "Trace: " . $e->getTraceAsString() . "\n";
}