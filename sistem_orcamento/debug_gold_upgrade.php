<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Plan;
use App\Models\Company;
use App\Models\Subscription;
use App\Models\UsageControl;
use App\Models\Payment;
use App\Services\PlanUpgradeService;
use Illuminate\Support\Facades\DB;

echo "🔍 Debug: Upgrade para Plano Ouro\n\n";

// Buscar planos
$planPrata = Plan::where('name', 'Prata')->first();
$planOuro = Plan::where('name', 'Ouro')->first();
$company = Company::first();

if (!$planPrata || !$planOuro || !$company) {
    echo "❌ Dados necessários não encontrados!\n";
    exit(1);
}

echo "📋 Planos:\n";
echo "   Prata - Budget Limit: {$planPrata->budget_limit}, isUnlimited(): " . ($planPrata->isUnlimited() ? 'true' : 'false') . "\n";
echo "   Ouro - Budget Limit: " . ($planOuro->budget_limit ?? 'NULL') . ", isUnlimited(): " . ($planOuro->isUnlimited() ? 'true' : 'false') . "\n\n";

try {
    DB::beginTransaction();
    
    // Limpar dados existentes
    UsageControl::where('company_id', $company->id)->delete();
    Subscription::where('company_id', $company->id)->delete();
    Payment::where('company_id', $company->id)->delete();
    
    // Criar assinatura Prata com uso
    $oldSubscription = Subscription::create([
        'company_id' => $company->id,
        'plan_id' => $planPrata->id,
        'status' => 'active',
        'starts_at' => now()->subMonth(),
        'ends_at' => now()->addMonth(),
        'billing_cycle' => 'monthly'
    ]);
    
    // Criar controle de uso com orçamentos restantes
    $oldUsageControl = UsageControl::create([
        'company_id' => $company->id,
        'subscription_id' => $oldSubscription->id,
        'year' => now()->year,
        'month' => now()->month,
        'budgets_used' => 45, // 5 restantes do plano
        'extra_budgets_purchased' => 50,
        'extra_budgets_used' => 20, // 30 extras restantes
        'inherited_budgets' => 10,
        'inherited_budgets_used' => 5 // 5 herdados restantes
    ]);
    
    echo "📊 Estado inicial (Plano Prata):\n";
    echo "   Orçamentos do plano: {$planPrata->budget_limit} (usados: {$oldUsageControl->budgets_used}, restantes: " . ($planPrata->budget_limit - $oldUsageControl->budgets_used) . ")\n";
    echo "   Extras: {$oldUsageControl->extra_budgets_purchased} (usados: {$oldUsageControl->extra_budgets_used}, restantes: " . ($oldUsageControl->extra_budgets_purchased - $oldUsageControl->extra_budgets_used) . ")\n";
    echo "   Herdados: {$oldUsageControl->inherited_budgets} (usados: {$oldUsageControl->inherited_budgets_used}, restantes: " . ($oldUsageControl->inherited_budgets - $oldUsageControl->inherited_budgets_used) . ")\n";
    
    $totalRemaining = ($planPrata->budget_limit - $oldUsageControl->budgets_used) + 
                     ($oldUsageControl->extra_budgets_purchased - $oldUsageControl->extra_budgets_used) + 
                     ($oldUsageControl->inherited_budgets - $oldUsageControl->inherited_budgets_used);
    echo "   Total restante: {$totalRemaining}\n\n";
    
    // Criar pagamento para upgrade
    $payment = Payment::create([
        'company_id' => $company->id,
        'plan_id' => $planOuro->id,
        'asaas_payment_id' => 'test_' . uniqid(),
        'amount' => $planOuro->monthly_price,
        'status' => 'confirmed',
        'payment_method' => 'pix',
        'billing_cycle' => 'monthly',
        'due_date' => now(),
        'confirmed_at' => now()
    ]);
    
    echo "🔄 Processando upgrade para Ouro...\n\n";
    
    // Usar PlanUpgradeService
    $upgradeService = new PlanUpgradeService();
    
    // Debug: Testar calculateInheritedBudgets
    $reflection = new \ReflectionClass($upgradeService);
    $method = $reflection->getMethod('calculateInheritedBudgets');
    $method->setAccessible(true);
    
    $calculatedInherited = $method->invoke($upgradeService, $oldSubscription, $planOuro, $oldUsageControl);
    echo "🧮 calculateInheritedBudgets retornou: {$calculatedInherited}\n";
    
    // Verificar lógica do finalInheritedBudgets
    $finalInheritedBudgets = $planOuro->isUnlimited() ? 0 : $calculatedInherited;
    echo "🎯 finalInheritedBudgets (após verificação isUnlimited): {$finalInheritedBudgets}\n\n";
    
    // Processar upgrade completo
    $newSubscription = $upgradeService->processUpgrade($oldSubscription, $planOuro, $payment);
    
    // Verificar resultado
    $newUsageControl = UsageControl::where('company_id', $company->id)
                                  ->where('subscription_id', $newSubscription->id)
                                  ->first();
    
    echo "✅ Upgrade processado!\n\n";
    echo "📊 Estado final (Plano Ouro):\n";
    echo "   Nova assinatura ID: {$newSubscription->id}\n";
    echo "   Orçamentos do plano: " . ($planOuro->budget_limit ?? 'ILIMITADO') . "\n";
    echo "   budgets_used: {$newUsageControl->budgets_used}\n";
    echo "   extra_budgets_purchased: {$newUsageControl->extra_budgets_purchased}\n";
    echo "   extra_budgets_used: {$newUsageControl->extra_budgets_used}\n";
    echo "   inherited_budgets: {$newUsageControl->inherited_budgets}\n";
    echo "   inherited_budgets_used: {$newUsageControl->inherited_budgets_used}\n\n";
    
    // Verificação
    echo "🎯 Verificação:\n";
    if ($newUsageControl->inherited_budgets == 0) {
        echo "   ✅ inherited_budgets zerado corretamente\n";
    } else {
        echo "   ❌ inherited_budgets NÃO foi zerado: {$newUsageControl->inherited_budgets}\n";
    }
    
    if ($newUsageControl->budgets_used == 0) {
        echo "   ✅ budgets_used zerado corretamente\n";
    } else {
        echo "   ❌ budgets_used NÃO foi zerado: {$newUsageControl->budgets_used}\n";
    }
    
    if ($newUsageControl->extra_budgets_purchased == 0) {
        echo "   ✅ extra_budgets_purchased zerado corretamente\n";
    } else {
        echo "   ❌ extra_budgets_purchased NÃO foi zerado: {$newUsageControl->extra_budgets_purchased}\n";
    }
    
    DB::rollBack(); // Não salvar as alterações
    echo "\n🔄 Transação revertida (dados de teste não salvos)\n";
    
} catch (\Exception $e) {
    DB::rollBack();
    echo "❌ Erro: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
}