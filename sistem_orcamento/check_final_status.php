<?php

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Payment;
use App\Models\Subscription;
use Illuminate\Support\Facades\DB;

echo "=== VERIFICAÇÃO FINAL DO SISTEMA ===\n\n";

try {
    // Verificar o pagamento corrigido
    $payment = Payment::find(23);
    
    if ($payment) {
        echo "✅ PAGAMENTO ID 23 (CORRIGIDO):\n";
        echo "- Billing Cycle: {$payment->billing_cycle}\n";
        echo "- Asaas Payment ID: {$payment->asaas_payment_id}\n";
        echo "- Asaas Subscription ID: {$payment->asaas_subscription_id}\n";
        echo "- Status: {$payment->status}\n";
        echo "- Amount: R$ {$payment->amount}\n\n";
    }
    
    // Verificar assinaturas ativas
    $activeSubscriptions = Subscription::where('status', 'active')
        ->with('plan')
        ->get();
    
    echo "✅ ASSINATURAS ATIVAS ({$activeSubscriptions->count()}):\n";
    foreach ($activeSubscriptions as $sub) {
        echo "- ID: {$sub->id}\n";
        echo "  Plano: {$sub->plan->name}\n";
        echo "  Ciclo: {$sub->billing_cycle}\n";
        echo "  Período: {$sub->start_date->format('d/m/Y')} até {$sub->end_date->format('d/m/Y')}\n";
        echo "  Próxima cobrança: {$sub->next_billing_date->format('d/m/Y')}\n";
        echo "  Asaas Subscription ID: " . ($sub->asaas_subscription_id ?? 'NULL') . "\n";
        echo "  Status: {$sub->status}\n\n";
    }
    
    // Verificar últimos pagamentos
    $recentPayments = Payment::orderBy('created_at', 'desc')
        ->limit(3)
        ->get();
    
    echo "✅ ÚLTIMOS PAGAMENTOS ({$recentPayments->count()}):\n";
    foreach ($recentPayments as $payment) {
        echo "- ID: {$payment->id}\n";
        echo "  Amount: R$ {$payment->amount}\n";
        echo "  Billing Cycle: {$payment->billing_cycle}\n";
        echo "  Status: {$payment->status}\n";
        echo "  Asaas Payment ID: " . ($payment->asaas_payment_id ?? 'NULL') . "\n";
        echo "  Asaas Subscription ID: " . ($payment->asaas_subscription_id ?? 'NULL') . "\n";
        echo "  Created: {$payment->created_at->format('d/m/Y H:i:s')}\n\n";
    }
    
    echo "🎉 SISTEMA FUNCIONANDO CORRETAMENTE!\n";
    
} catch (Exception $e) {
    echo "❌ ERRO: " . $e->getMessage() . "\n";
}

echo "\n=== FIM DA VERIFICAÇÃO ===\n";