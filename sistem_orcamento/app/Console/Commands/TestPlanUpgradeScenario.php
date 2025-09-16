<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Company;
use App\Models\Plan;
use App\Models\Subscription;
use App\Models\UsageControl;
use App\Services\PlanUpgradeService;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class TestPlanUpgradeScenario extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:plan-upgrade-scenario {--reset : Resetar dados antes do teste}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Testa o cenário específico de upgrade de plano: Prata (45/50 usados + 50 extras) para Bronze';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🧪 Testando cenário de upgrade de plano...');
        
        if ($this->option('reset')) {
            $this->resetTestData();
        }
        
        // Buscar planos
        $planPrata = Plan::where('name', 'Prata')->first();
        $planBronze = Plan::where('name', 'Bronze')->first();
        
        if (!$planPrata || !$planBronze) {
            $this->error('❌ Planos Prata ou Bronze não encontrados!');
            return 1;
        }
        
        $this->info("📋 Plano Prata: {$planPrata->budget_limit} orçamentos - R$ {$planPrata->monthly_price}");
        $this->info("📋 Plano Bronze: {$planBronze->budget_limit} orçamentos - R$ {$planBronze->monthly_price}");
        
        // Buscar empresa de teste
        $company = Company::first();
        if (!$company) {
            $this->error('❌ Nenhuma empresa encontrada!');
            return 1;
        }
        
        // Criar cenário: Assinatura Prata com 45/50 usados + 50 extras comprados
        $this->createTestScenario($company, $planPrata);
        
        // Fazer upgrade para Bronze
        $this->performUpgrade($company, $planBronze);
        
        return 0;
    }
    
    private function resetTestData()
    {
        $this->info('🔄 Resetando dados de teste...');
        
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
        
        // Criar UsageControl com o cenário específico
        $usageControl = UsageControl::create([
            'company_id' => $company->id,
            'subscription_id' => $subscription->id,
            'year' => now()->year,
            'month' => now()->month,
            'budgets_used' => 45, // 45 dos 50 do plano Prata
            'extra_budgets_purchased' => 50, // 50 extras comprados
            'extra_budgets_used' => 0, // Nenhum extra usado
            'inherited_budgets' => 0,
            'inherited_budgets_used' => 0
        ]);
        
        $this->info("✅ Assinatura Prata criada (ID: {$subscription->id})");
        $this->info("📊 Estado atual:");
        $this->info("   - Orçamentos do plano: {$planPrata->budget_limit}");
        $this->info("   - Orçamentos usados: {$usageControl->budgets_used}");
        $this->info("   - Orçamentos restantes do plano: " . ($planPrata->budget_limit - $usageControl->budgets_used));
        $this->info("   - Extras comprados: {$usageControl->extra_budgets_purchased}");
        $this->info("   - Extras usados: {$usageControl->extra_budgets_used}");
        $this->info("   - Total não utilizado: " . (($planPrata->budget_limit - $usageControl->budgets_used) + ($usageControl->extra_budgets_purchased - $usageControl->extra_budgets_used)));
    }
    
    private function performUpgrade(Company $company, Plan $planBronze)
    {
        $this->info('\n🔄 Realizando upgrade para plano Bronze...');
        
        $oldSubscription = Subscription::where('company_id', $company->id)->where('status', 'active')->first();
        $oldUsageControl = UsageControl::where('company_id', $company->id)->first();
        
        // Simular o upgrade usando o PlanUpgradeService
        $upgradeService = new PlanUpgradeService();
        
        // Calcular orçamentos herdados manualmente para verificar
        $remainingFromPlan = max(0, 50 - 45); // 5 restantes do plano Prata
        $remainingFromExtras = max(0, 50 - 0); // 50 extras não usados
        $totalRemaining = $remainingFromPlan + $remainingFromExtras; // 55 total
        
        // Agora herda 100% dos orçamentos não utilizados
         $expectedInherited = $totalRemaining; // 55
        
        $this->info("🧮 Cálculo esperado:");
        $this->info("   - Restantes do plano: {$remainingFromPlan}");
        $this->info("   - Extras não usados: {$remainingFromExtras}");
        $this->info("   - Total não utilizado: {$totalRemaining}");
        $this->info("   - Tipo: Downgrade (100% de herança)");
        $this->info("   - Orçamentos herdados esperados: {$expectedInherited}");
        
        // Criar nova assinatura Bronze
        $newSubscription = Subscription::create([
            'company_id' => $company->id,
            'plan_id' => $planBronze->id,
            'status' => 'active',
            'starts_at' => now(),
            'ends_at' => now()->addMonth(),
            'billing_cycle' => 'monthly'
        ]);
        
        // Desativar assinatura antiga
        $oldSubscription->update(['status' => 'cancelled']);
        
        // Usar reflexão para acessar o método privado calculateInheritedBudgets
        $reflection = new \ReflectionClass($upgradeService);
        $method = $reflection->getMethod('calculateInheritedBudgets');
        $method->setAccessible(true);
        
        $inheritedBudgets = $method->invoke($upgradeService, $oldSubscription, $planBronze, $oldUsageControl);
        
        // Deletar UsageControl existente para evitar conflito de chave única
         UsageControl::where('company_id', $company->id)
                    ->where('year', now()->year)
                    ->where('month', now()->month)
                    ->delete();
         
         // Criar novo UsageControl
         $newUsageControl = UsageControl::create([
             'company_id' => $company->id,
             'subscription_id' => $newSubscription->id,
             'year' => now()->year,
             'month' => now()->month,
             'budgets_used' => 0,
             'extra_budgets_purchased' => 0,
             'extra_budgets_used' => 0,
             'inherited_budgets' => $inheritedBudgets,
             'inherited_budgets_used' => 0
         ]);
        
        $this->info("\n✅ Upgrade realizado!");
        $this->info("📊 Novo estado (Plano Bronze):");
        $this->info("   - Orçamentos do plano: {$planBronze->budget_limit}");
        $this->info("   - Orçamentos usados: {$newUsageControl->budgets_used}");
        $this->info("   - Extras comprados: {$newUsageControl->extra_budgets_purchased}");
        $this->info("   - Orçamentos herdados: {$newUsageControl->inherited_budgets}");
        $this->info("   - Total disponível: " . ($planBronze->budget_limit + $newUsageControl->inherited_budgets));
        
        // Verificar se está correto
        $totalAvailable = $planBronze->budget_limit + $newUsageControl->inherited_budgets;
        $this->info("\n🎯 Resultado:");
        if ($newUsageControl->inherited_budgets == $expectedInherited) {
            $this->info("   ✅ Orçamentos herdados corretos: {$newUsageControl->inherited_budgets}");
        } else {
            $this->error("   ❌ Orçamentos herdados incorretos! Esperado: {$expectedInherited}, Atual: {$newUsageControl->inherited_budgets}");
        }
        
        $this->info("   📈 Total de orçamentos disponíveis: {$totalAvailable}");
    }
}
