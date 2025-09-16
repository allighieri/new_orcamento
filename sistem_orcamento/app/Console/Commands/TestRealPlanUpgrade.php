<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Company;
use App\Models\Plan;
use App\Models\Subscription;
use App\Models\UsageControl;
use App\Models\Payment;
use App\Services\PlanUpgradeService;
use Illuminate\Support\Facades\DB;

class TestRealPlanUpgrade extends Command
{
    protected $signature = 'test:real-plan-upgrade {--reset}';
    protected $description = 'Testa o cenário real de upgrade de plano usando o PlanUpgradeService';

    public function handle()
    {
        $this->info('🧪 Testando cenário REAL de upgrade de plano...');
        
        if ($this->option('reset')) {
            $this->resetTestData();
        }
        
        // Buscar empresa e planos
        $company = Company::first();
        if (!$company) {
            $this->error('❌ Nenhuma empresa encontrada!');
            return 1;
        }
        
        $planPrata = Plan::where('name', 'Prata')->first();
        $planBronze = Plan::where('name', 'Bronze')->first();
        
        if (!$planPrata || !$planBronze) {
            $this->error('❌ Planos Prata ou Bronze não encontrados!');
            return 1;
        }
        
        $this->info("📋 Plano Prata: {$planPrata->budget_limit} orçamentos - R$ {$planPrata->monthly_price}");
        $this->info("📋 Plano Bronze: {$planBronze->budget_limit} orçamentos - R$ {$planBronze->monthly_price}");
        
        // Criar cenário inicial
        $subscription = $this->createTestScenario($company, $planPrata);
        
        // Realizar upgrade usando o serviço real
        $this->performRealUpgrade($subscription, $planBronze);
        
        return 0;
    }
    
    private function resetTestData()
    {
        $this->info('🔄 Resetando dados de teste...');
        
        DB::table('payments')->where('company_id', 1)->delete();
        DB::table('usage_controls')->where('company_id', 1)->delete();
        DB::table('subscriptions')->where('company_id', 1)->delete();
    }
    
    private function createTestScenario(Company $company, Plan $planPrata)
    {
        $this->info('\n🏗️  Criando cenário de teste...');
        
        // Criar assinatura Prata
        $subscription = Subscription::create([
            'company_id' => $company->id,
            'plan_id' => $planPrata->id,
            'status' => 'active',
            'starts_at' => now(),
            'ends_at' => now()->addMonth(),
            'billing_cycle' => 'monthly'
        ]);
        
        // Criar UsageControl com o cenário específico do usuário
        $usageControl = UsageControl::create([
            'company_id' => $company->id,
            'subscription_id' => $subscription->id,
            'year' => now()->year,
            'month' => now()->month,
            'budgets_used' => 45, // 45 dos 50 do plano Prata
            'extra_budgets_purchased' => 50, // 50 extras comprados
            'extra_budgets_used' => 20, // 20 extras usados
            'inherited_budgets' => 0,
            'inherited_budgets_used' => 0
        ]);
        
        $this->info("✅ Assinatura Prata criada (ID: {$subscription->id})");
        $this->info("📊 Estado inicial:");
        $this->info("   - Orçamentos do plano: {$planPrata->budget_limit}");
        $this->info("   - Orçamentos usados: {$usageControl->budgets_used}");
        $this->info("   - Orçamentos restantes do plano: " . ($planPrata->budget_limit - $usageControl->budgets_used));
        $this->info("   - Extras comprados: {$usageControl->extra_budgets_purchased}");
        $this->info("   - Extras usados: {$usageControl->extra_budgets_used}");
        $this->info("   - Extras restantes: " . ($usageControl->extra_budgets_purchased - $usageControl->extra_budgets_used));
        $this->info("   - Total não utilizado: " . (($planPrata->budget_limit - $usageControl->budgets_used) + ($usageControl->extra_budgets_purchased - $usageControl->extra_budgets_used)));
        
        return $subscription;
    }
    
    private function performRealUpgrade(Subscription $oldSubscription, Plan $newPlan)
    {
        $this->info('\n🔄 Realizando upgrade REAL usando PlanUpgradeService...');
        
        // Criar pagamento fictício
        $payment = Payment::create([
            'company_id' => $oldSubscription->company_id,
            'subscription_id' => $oldSubscription->id,
            'plan_id' => $newPlan->id,
            'amount' => $newPlan->monthly_price,
            'status' => 'confirmed',
            'billing_cycle' => 'monthly',
            'payment_method' => 'credit_card',
            'asaas_payment_id' => 'test_' . time(),
            'due_date' => now()->addDays(7),
            'external_id' => 'test_' . time()
        ]);
        
        // Usar o PlanUpgradeService real
        $upgradeService = new PlanUpgradeService();
        
        try {
            $newSubscription = $upgradeService->processUpgrade($oldSubscription, $newPlan, $payment);
            
            // Verificar o resultado
            $newUsageControl = UsageControl::where('company_id', $oldSubscription->company_id)
                ->where('subscription_id', $newSubscription->id)
                ->where('year', now()->year)
                ->where('month', now()->month)
                ->first();
            
            $this->info("\n✅ Upgrade realizado com sucesso!");
            $this->info("📊 Novo estado (Plano {$newPlan->name}):");
            $this->info("   - Nova assinatura ID: {$newSubscription->id}");
            $this->info("   - Orçamentos do plano: {$newPlan->budget_limit}");
            $this->info("   - Orçamentos usados: {$newUsageControl->budgets_used}");
            $this->info("   - Extras comprados: {$newUsageControl->extra_budgets_purchased}");
            $this->info("   - Extras usados: {$newUsageControl->extra_budgets_used}");
            $this->info("   - Orçamentos herdados: {$newUsageControl->inherited_budgets}");
            $this->info("   - Herdados usados: {$newUsageControl->inherited_budgets_used}");
            $this->info("   - Total disponível: " . ($newPlan->budget_limit + $newUsageControl->inherited_budgets));
            
            // Verificar se está correto
            $expectedInherited = 35; // 5 restantes + 30 extras não usados
            $this->info("\n🎯 Verificação:");
            
            if ($newUsageControl->budgets_used == 0) {
                $this->info("   ✅ budgets_used zerado corretamente");
            } else {
                $this->error("   ❌ budgets_used não foi zerado: {$newUsageControl->budgets_used}");
            }
            
            if ($newUsageControl->extra_budgets_purchased == 0) {
                $this->info("   ✅ extra_budgets_purchased zerado corretamente");
            } else {
                $this->error("   ❌ extra_budgets_purchased não foi zerado: {$newUsageControl->extra_budgets_purchased}");
            }
            
            if ($newUsageControl->extra_budgets_used == 0) {
                $this->info("   ✅ extra_budgets_used zerado corretamente");
            } else {
                $this->error("   ❌ extra_budgets_used não foi zerado: {$newUsageControl->extra_budgets_used}");
            }
            
            if ($newUsageControl->inherited_budgets == $expectedInherited) {
                $this->info("   ✅ inherited_budgets correto: {$newUsageControl->inherited_budgets}");
            } else {
                $this->error("   ❌ inherited_budgets incorreto! Esperado: {$expectedInherited}, Atual: {$newUsageControl->inherited_budgets}");
            }
            
            $totalAvailable = $newPlan->budget_limit + $newUsageControl->inherited_budgets;
            $this->info("   📈 Total de orçamentos disponíveis: {$totalAvailable}");
            
        } catch (\Exception $e) {
            $this->error("❌ Erro ao processar upgrade: " . $e->getMessage());
            $this->error("Stack trace: " . $e->getTraceAsString());
        }
    }
}