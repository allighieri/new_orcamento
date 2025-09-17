<?php

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Payment;
use App\Jobs\ProcessAsaasWebhook;
use Illuminate\Support\Facades\Log;

echo "=== TESTE DE WEBHOOK PIX (PAYMENT_RECEIVED) ===\n\n";

// Buscar um pagamento existente para testar (qualquer status)
$payment = Payment::whereNotNull('asaas_payment_id')
    ->orderBy('id', 'desc')
    ->first();

if (!$payment) {
    echo "❌ Nenhum pagamento encontrado para teste\n";
    exit(1);
}

echo "✅ Pagamento encontrado para teste:\n";
echo "   - ID: {$payment->id}\n";
echo "   - Asaas Payment ID: {$payment->asaas_payment_id}\n";
echo "   - Status atual: {$payment->status}\n";
echo "   - Company ID: {$payment->company_id}\n\n";

// Simular payload do webhook PIX (PAYMENT_RECEIVED)
$webhookPayload = [
    'event' => 'PAYMENT_RECEIVED',
    'payment' => [
        'id' => $payment->asaas_payment_id,
        'status' => 'RECEIVED',
        'value' => $payment->amount,
        'netValue' => $payment->amount * 0.97, // Simular taxa
        'originalValue' => $payment->amount,
        'interestValue' => 0,
        'description' => 'Pagamento PIX - Teste',
        'billingType' => 'PIX',
        'pixTransaction' => [
            'endToEndIdentifier' => 'E12345678202501171234567890123456',
            'txid' => 'PIX' . time(),
            'paidOutsideAsaas' => false
        ],
        'subscription' => $payment->asaas_subscription_id,
        'installment' => null,
        'paymentDate' => date('Y-m-d'),
        'clientPaymentDate' => date('Y-m-d'),
        'installmentNumber' => null,
        'invoiceUrl' => 'https://sandbox.asaas.com/i/' . $payment->asaas_payment_id,
        'bankSlipUrl' => null,
        'transactionReceiptUrl' => 'https://sandbox.asaas.com/comprovantes/' . $payment->asaas_payment_id
    ]
];

echo "📤 Simulando webhook PAYMENT_RECEIVED...\n";
echo "   - Event: {$webhookPayload['event']}\n";
echo "   - Status: {$webhookPayload['payment']['status']}\n";
echo "   - Billing Type: {$webhookPayload['payment']['billingType']}\n\n";

// Processar o webhook
try {
    $startTime = microtime(true);
    
    // Criar e processar o job com dependências resolvidas
    $job = new ProcessAsaasWebhook($webhookPayload);
    $asaasService = app(\App\Services\AsaasService::class);
    $planUpgradeService = app(\App\Services\PlanUpgradeService::class);
    $job->handle($asaasService, $planUpgradeService);
    
    $endTime = microtime(true);
    $executionTime = ($endTime - $startTime) * 1000;
    
    echo "✅ Webhook processado com sucesso!\n";
    echo "   - Tempo de execução: " . number_format($executionTime, 2) . "ms\n\n";
    
    // Verificar se o pagamento foi atualizado
    $payment->refresh();
    echo "📊 Status do pagamento após processamento:\n";
    echo "   - Status: {$payment->status}\n";
    echo "   - Confirmed At: " . ($payment->confirmed_at ? $payment->confirmed_at->format('Y-m-d H:i:s') : 'null') . "\n\n";
    
    echo "🎯 Evento PaymentConfirmed deve ter sido disparado!\n";
    echo "   - Payment ID: {$payment->id}\n";
    echo "   - Company ID: {$payment->company_id}\n";
    echo "   - Status: {$payment->status}\n\n";
    
    echo "💡 Verifique:\n";
    echo "   1. Os logs do queue worker\n";
    echo "   2. A página de teste WebSocket (test-websocket-pix.html)\n";
    echo "   3. As páginas de checkout se estão escutando o evento\n\n";
    
} catch (Exception $e) {
    echo "❌ Erro ao processar webhook:\n";
    echo "   - Erro: {$e->getMessage()}\n";
    echo "   - Arquivo: {$e->getFile()}:{$e->getLine()}\n";
    echo "   - Trace: {$e->getTraceAsString()}\n";
}

echo "=== FIM DO TESTE ===\n";