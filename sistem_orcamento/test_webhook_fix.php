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

echo "=== TESTE DA CORREÇÃO DO WEBHOOK ===\n\n";

// Simular um pagamento anual que deveria criar uma subscription anual
// mas a empresa já tem uma subscription mensal ativa

// 1. Verificar estado atual
echo "1. Estado atual da empresa 1:\n";
$activeSubscriptions = Subscription::where('company_id', 1)
    ->where('status', 'active')
    ->get();
    
foreach ($activeSubscriptions as $sub) {
    echo "   - Subscription ID {$sub->id}: {$sub->billing_cycle} | {$sub->status}\n";
}

// 2. Criar um payment anual de teste
echo "\n2. Criando payment anual de teste...\n";
$testPayment = Payment::create([
    'company_id' => 1,
    'plan_id' => 1,
    'billing_cycle' => 'annual',
    'amount' => 1200.00,
    'billing_type' => 'PIX',
    'type' => 'subscription',
    'status' => 'RECEIVED',
    'due_date' => now()->addDay(),
    'asaas_payment_id' => 'pay_test_' . time(),
    'asaas_subscription_id' => 'sub_test_' . time(),
    'created_at' => now(),
    'updated_at' => now()
]);

echo "   Payment criado: ID {$testPayment->id} | {$testPayment->billing_cycle} | {$testPayment->asaas_subscription_id}\n";

// 3. Simular o webhook processando este payment
echo "\n3. Simulando processamento do webhook...\n";

// Criar instância do AsaasService
$asaasService = app(\App\Services\AsaasService::class);
$webhookController = new WebhookController($asaasService);

// Usar reflexão para chamar o método privado
$reflection = new ReflectionClass($webhookController);
$method = $reflection->getMethod('handleSubscriptionPayment');
$method->setAccessible(true);

try {
    $method->invoke($webhookController, $testPayment);
    echo "   ✅ Webhook processado com sucesso\n";
} catch (Exception $e) {
    echo "   ❌ Erro no webhook: " . $e->getMessage() . "\n";
    echo "   Stack trace: " . $e->getTraceAsString() . "\n";
}

// 4. Verificar resultado
echo "\n4. Estado após processamento:\n";
$subscriptionsAfter = Subscription::where('company_id', 1)
    ->orderBy('created_at', 'desc')
    ->get();
    
foreach ($subscriptionsAfter as $sub) {
    echo "   - Subscription ID {$sub->id}: {$sub->billing_cycle} | {$sub->status} | Asaas: {$sub->asaas_subscription_id} | Created: {$sub->created_at}\n";
}

// 5. Verificar se foi criada subscription anual
$newAnnualSub = Subscription::where('company_id', 1)
    ->where('billing_cycle', 'annual')
    ->where('asaas_subscription_id', $testPayment->asaas_subscription_id)
    ->where('status', 'active')
    ->first();
    
if ($newAnnualSub) {
    echo "\n✅ SUCESSO: Subscription anual criada corretamente!\n";
    echo "   - ID: {$newAnnualSub->id}\n";
    echo "   - Billing Cycle: {$newAnnualSub->billing_cycle}\n";
    echo "   - Status: {$newAnnualSub->status}\n";
    echo "   - Asaas Subscription ID: {$newAnnualSub->asaas_subscription_id}\n";
} else {
    echo "\n❌ FALHA: Subscription anual não foi criada\n";
}

// 6. Limpar dados de teste
echo "\n6. Limpando dados de teste...\n";
$testPayment->delete();
if ($newAnnualSub) {
    $newAnnualSub->delete();
}
echo "   Dados de teste removidos\n";

echo "\n=== TESTE CONCLUÍDO ===\n";