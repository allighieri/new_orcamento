<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

// Buscar o último pagamento com status 'received'
$payment = \App\Models\Payment::where('status', 'received')
    ->orderBy('id', 'desc')
    ->first();

if (!$payment) {
    echo "Nenhum pagamento com status 'received' encontrado.\n";
    exit;
}

echo "Processando pagamento ID: {$payment->id}\n";
echo "Company ID: {$payment->company_id}\n";
echo "Plan ID: {$payment->plan_id}\n";
echo "Billing Cycle: {$payment->billing_cycle}\n";
echo "Amount: {$payment->amount}\n";
echo "Status: {$payment->status}\n";
echo "\n";

// Simular o webhook data
$webhookData = [
    'event' => 'PAYMENT_RECEIVED',
    'payment' => [
        'id' => $payment->asaas_payment_id,
        'status' => 'RECEIVED',
        'value' => $payment->amount,
        'netValue' => $payment->amount,
        'billingType' => 'PIX'
    ]
];

try {
    // Criar e processar o job
    $job = new \App\Jobs\ProcessAsaasWebhook($webhookData);
    $job->handle(
        app(\App\Services\AsaasService::class),
        app(\App\Services\PlanUpgradeService::class)
    );
    
    echo "Job processado com sucesso!\n";
    
    // Verificar se a assinatura foi criada
    $newSubscription = \App\Models\Subscription::where('company_id', $payment->company_id)
        ->where('status', 'active')
        ->orderBy('id', 'desc')
        ->first();
        
    if ($newSubscription) {
        echo "Nova assinatura criada: ID {$newSubscription->id}\n";
        echo "Plan ID: {$newSubscription->plan_id}\n";
        echo "Billing Cycle: {$newSubscription->billing_cycle}\n";
    } else {
        echo "ERRO: Nenhuma assinatura ativa encontrada após processamento!\n";
    }
    
} catch (Exception $e) {
    echo "Erro ao processar job: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
}