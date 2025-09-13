<?php

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Payment;
use Illuminate\Support\Facades\DB;

echo "=== CORREÇÃO DO PAGAMENTO ID 23 ===\n\n";

try {
    DB::beginTransaction();
    
    // Buscar o pagamento ID 23
    $payment = Payment::find(23);
    
    if (!$payment) {
        throw new Exception('Pagamento ID 23 não encontrado');
    }
    
    echo "Dados atuais do pagamento ID 23:\n";
    echo "- Company ID: {$payment->company_id}\n";
    echo "- Plan ID: {$payment->plan_id}\n";
    echo "- Amount: R$ {$payment->amount}\n";
    echo "- Billing Type: {$payment->billing_type}\n";
    echo "- Billing Cycle: {$payment->billing_cycle}\n";
    echo "- Status: {$payment->status}\n";
    echo "- Asaas Payment ID: " . ($payment->asaas_payment_id ?? 'NULL') . "\n";
    echo "- Asaas Subscription ID: {$payment->asaas_subscription_id}\n";
    echo "- Due Date: {$payment->due_date}\n\n";
    
    // Corrigir os dados
    $payment->update([
        'billing_cycle' => 'annual',
        'asaas_payment_id' => 'pay_z2nxldgf2obteybq', // ID do webhook que estava falhando
        'updated_at' => now()
    ]);
    
    echo "✅ Pagamento atualizado com sucesso!\n\n";
    
    // Verificar os dados após a correção
    $payment->refresh();
    
    echo "Dados corrigidos do pagamento ID 23:\n";
    echo "- Billing Cycle: {$payment->billing_cycle}\n";
    echo "- Asaas Payment ID: {$payment->asaas_payment_id}\n";
    echo "- Asaas Subscription ID: {$payment->asaas_subscription_id}\n\n";
    
    DB::commit();
    
    echo "🎉 CORREÇÃO CONCLUÍDA COM SUCESSO!\n";
    
} catch (Exception $e) {
    DB::rollBack();
    echo "❌ ERRO: " . $e->getMessage() . "\n";
}

echo "\n=== FIM DA CORREÇÃO ===\n";