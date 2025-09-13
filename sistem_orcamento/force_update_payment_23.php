<?php

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

echo "=== ATUALIZAÇÃO FORÇADA DO PAGAMENTO ID 23 ===\n\n";

try {
    // Usar query direta para forçar a atualização
    $updated = DB::table('payments')
        ->where('id', 23)
        ->update([
            'billing_cycle' => 'annual',
            'asaas_payment_id' => 'pay_z2nxldgf2obteybq',
            'updated_at' => now()
        ]);
    
    if ($updated) {
        echo "✅ {$updated} registro(s) atualizado(s) com sucesso!\n\n";
    } else {
        echo "❌ Nenhum registro foi atualizado.\n\n";
    }
    
    // Verificar os dados após a atualização
    $payment = DB::table('payments')->where('id', 23)->first();
    
    if ($payment) {
        echo "Dados atualizados do pagamento ID 23:\n";
        echo "- Company ID: {$payment->company_id}\n";
        echo "- Plan ID: {$payment->plan_id}\n";
        echo "- Amount: R$ {$payment->amount}\n";
        echo "- Billing Type: {$payment->billing_type}\n";
        echo "- Billing Cycle: {$payment->billing_cycle}\n";
        echo "- Status: {$payment->status}\n";
        echo "- Asaas Payment ID: " . ($payment->asaas_payment_id ?? 'NULL') . "\n";
        echo "- Asaas Subscription ID: {$payment->asaas_subscription_id}\n";
        echo "- Due Date: {$payment->due_date}\n";
        echo "- Updated At: {$payment->updated_at}\n\n";
    }
    
    echo "🎉 ATUALIZAÇÃO CONCLUÍDA!\n";
    
} catch (Exception $e) {
    echo "❌ ERRO: " . $e->getMessage() . "\n";
}

echo "\n=== FIM DA ATUALIZAÇÃO ===\n";