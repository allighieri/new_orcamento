<?php

require_once 'vendor/autoload.php';

// Carregar configuração do Laravel
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Payment;
use App\Models\Subscription;
use App\Http\Controllers\WebhookController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

echo "=== TESTE COM PAGAMENTO REAL (Payment ID 23) ===\n\n";

// 1. Verificar estado atual do Payment 23
$payment23 = Payment::find(23);
if (!$payment23) {
    echo "❌ Payment 23 não encontrado\n";
    exit;
}

echo "1. Payment 23 atual:\n";
echo "   - ID: {$payment23->id}\n";
echo "   - Company ID: {$payment23->company_id}\n";
echo "   - Billing Cycle: {$payment23->billing_cycle}\n";
echo "   - Asaas Subscription ID: {$payment23->asaas_subscription_id}\n";
echo "   - Status: {$payment23->status}\n";

// 2. Verificar subscriptions atuais da empresa
echo "\n2. Subscriptions atuais da empresa {$payment23->company_id}:\n";
$currentSubs = Subscription::where('company_id', $payment23->company_id)
    ->orderBy('created_at', 'desc')
    ->get();
    
foreach ($currentSubs as $sub) {
    echo "   - ID {$sub->id}: {$sub->billing_cycle} | {$sub->status} | Asaas: " . ($sub->asaas_subscription_id ?: 'NULL') . "\n";
}

// 3. Verificar se existe subscription com o mesmo asaas_subscription_id
$conflictingSub = Subscription::where('asaas_subscription_id', $payment23->asaas_subscription_id)
    ->first();
    
if ($conflictingSub) {
    echo "\n3. 🚨 CONFLITO DETECTADO:\n";
    echo "   - Subscription ID {$conflictingSub->id} já usa o asaas_subscription_id: {$payment23->asaas_subscription_id}\n";
    echo "   - Subscription é {$conflictingSub->billing_cycle} mas Payment é {$payment23->billing_cycle}\n";
    echo "   - Status da Subscription: {$conflictingSub->status}\n";
    
    // Cancelar a subscription conflitante para simular o cenário correto
    echo "\n   Cancelando subscription conflitante para teste...\n";
    $conflictingSub->update([
        'status' => 'cancelled',
        'cancelled_at' => now()
    ]);
    echo "   ✅ Subscription {$conflictingSub->id} cancelada\n";
}

// 4. Simular o webhook processando este payment
echo "\n4. Simulando processamento do webhook...\n";

// Criar instância do AsaasService
$asaasService = app(\App\Services\AsaasService::class);
$webhookController = new WebhookController($asaasService);

// Usar reflexão para chamar o método privado
$reflection = new ReflectionClass($webhookController);
$method = $reflection->getMethod('handleSubscriptionPayment');
$method->setAccessible(true);

try {
    $method->invoke($webhookController, $payment23);
    echo "   ✅ Webhook processado com sucesso\n";
} catch (Exception $e) {
    echo "   ❌ Erro no webhook: " . $e->getMessage() . "\n";
    echo "   Stack trace: " . $e->getTraceAsString() . "\n";
}

// 5. Verificar resultado
echo "\n5. Estado após processamento:\n";
$subscriptionsAfter = Subscription::where('company_id', $payment23->company_id)
    ->orderBy('created_at', 'desc')
    ->get();
    
foreach ($subscriptionsAfter as $sub) {
    echo "   - ID {$sub->id}: {$sub->billing_cycle} | {$sub->status} | Asaas: " . ($sub->asaas_subscription_id ?: 'NULL') . " | Created: {$sub->created_at}\n";
}

// 6. Verificar se foi criada subscription anual correta
$correctAnnualSub = Subscription::where('company_id', $payment23->company_id)
    ->where('billing_cycle', 'annual')
    ->where('asaas_subscription_id', $payment23->asaas_subscription_id)
    ->where('status', 'active')
    ->first();
    
if ($correctAnnualSub) {
    echo "\n✅ SUCESSO: Subscription anual criada corretamente para Payment 23!\n";
    echo "   - Subscription ID: {$correctAnnualSub->id}\n";
    echo "   - Billing Cycle: {$correctAnnualSub->billing_cycle}\n";
    echo "   - Status: {$correctAnnualSub->status}\n";
    echo "   - Asaas Subscription ID: {$correctAnnualSub->asaas_subscription_id}\n";
    echo "   - Start Date: {$correctAnnualSub->start_date}\n";
    echo "   - End Date: {$correctAnnualSub->end_date}\n";
} else {
    echo "\n❌ FALHA: Subscription anual não foi criada para Payment 23\n";
}

echo "\n=== TESTE CONCLUÍDO ===\n";