<?php

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Payment;
use App\Models\Subscription;
use App\Models\Company;
use App\Models\Plan;
use Illuminate\Support\Facades\DB;

echo "=== TESTE DE ASSINATURA ANUAL ===\n\n";

try {
    DB::beginTransaction();
    
    // Buscar empresa e plano
    $company = Company::first();
    $plan = Plan::where('name', 'Bronze')->first();
    
    if (!$company || !$plan) {
        throw new Exception('Empresa ou plano não encontrado');
    }
    
    echo "Empresa: {$company->fantasy_name}\n";
    echo "Plano: {$plan->name} - R$ {$plan->yearly_price}\n\n";
    
    // Cancelar assinatura atual se existir
    $currentSubscription = Subscription::where('company_id', $company->id)
        ->where('status', 'active')
        ->first();
        
    if ($currentSubscription) {
        $currentSubscription->update([
            'status' => 'cancelled',
            'cancelled_at' => now()
        ]);
        echo "✅ Assinatura atual cancelada (ID: {$currentSubscription->id})\n";
    }
    
    // Criar novo pagamento de assinatura anual
    $payment = Payment::create([
        'company_id' => $company->id,
        'plan_id' => $plan->id,
        'amount' => $plan->yearly_price,
        'billing_cycle' => 'annual',
        'billing_type' => 'PIX',
        'due_date' => now()->addDay(),
        'status' => 'RECEIVED', // Simular pagamento confirmado
        'asaas_subscription_id' => 'sub_test_' . uniqid(),
        'created_at' => now(),
        'updated_at' => now()
    ]);
    
    echo "✅ Pagamento criado (ID: {$payment->id})\n";
    echo "Asaas Subscription ID: {$payment->asaas_subscription_id}\n\n";
    
    // Simular processamento do webhook
    $paymentData = [
        'id' => 'pay_test_' . uniqid(),
        'status' => 'RECEIVED',
        'subscription' => $payment->asaas_subscription_id
    ];
    
    // Criar nova assinatura
    $startDate = now();
    $endDate = $startDate->copy()->addYear();
    
    $newSubscription = Subscription::create([
        'company_id' => $payment->company_id,
        'plan_id' => $payment->plan_id,
        'billing_cycle' => 'annual',
        'status' => 'active',
        'start_date' => $startDate,
        'end_date' => $endDate,
        'next_billing_date' => $startDate->copy()->addMonth(), // Próxima cobrança mensal
        'amount_paid' => $payment->amount,
        'asaas_subscription_id' => $payment->asaas_subscription_id,
        'created_at' => now(),
        'updated_at' => now()
    ]);
    
    echo "✅ Nova assinatura criada (ID: {$newSubscription->id})\n";
    echo "Período: {$newSubscription->start_date->format('d/m/Y')} até {$newSubscription->end_date->format('d/m/Y')}\n";
    echo "Próxima cobrança: {$newSubscription->next_billing_date->format('d/m/Y')}\n";
    echo "Status: {$newSubscription->status}\n\n";
    
    DB::commit();
    
    echo "🎉 TESTE CONCLUÍDO COM SUCESSO!\n\n";
    
    // Verificar resultado final
    $activeSubscription = Subscription::where('company_id', $company->id)
        ->where('status', 'active')
        ->first();
        
    if ($activeSubscription) {
        echo "✅ Assinatura ativa encontrada:\n";
        echo "ID: {$activeSubscription->id}\n";
        echo "Plano: {$activeSubscription->plan->name}\n";
        echo "Ciclo: {$activeSubscription->billing_cycle}\n";
        echo "Asaas Subscription ID: {$activeSubscription->asaas_subscription_id}\n";
    } else {
        echo "❌ Nenhuma assinatura ativa encontrada!\n";
    }
    
} catch (Exception $e) {
    DB::rollBack();
    echo "❌ ERRO: " . $e->getMessage() . "\n";
}

echo "\n=== FIM DO TESTE ===\n";