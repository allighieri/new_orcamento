<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\UsageControl;
use App\Models\Subscription;
use App\Models\Plan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class FixPlanUpgradeData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fix:plan-upgrade-data {--dry-run : Apenas simular sem fazer alterações}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Corrige dados de upgrade de planos, transferindo orçamentos não utilizados para inherited_budgets';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $dryRun = $this->option('dry-run');
        
        if ($dryRun) {
            $this->info('🔍 Modo simulação ativado - nenhuma alteração será feita');
        }
        
        $this->info('🚀 Iniciando correção de dados de upgrade de planos...');
        
        // Buscar todos os controles de uso que podem ter problemas
        $usageControls = UsageControl::with(['subscription.plan'])
            ->where(function($query) {
                $query->where('inherited_budgets', '>', 0)
                      ->orWhere('budgets_used', '>', 0)
                      ->orWhere('extra_budgets_used', '>', 0);
            })
            ->get();
            
        $this->info("📊 Encontrados {$usageControls->count()} registros para análise");
        
        $fixed = 0;
        $errors = 0;
        
        foreach ($usageControls as $usageControl) {
            try {
                $this->processUsageControl($usageControl, $dryRun, $fixed);
            } catch (\Exception $e) {
                $errors++;
                $this->error("❌ Erro ao processar UsageControl ID {$usageControl->id}: {$e->getMessage()}");
                Log::error('Erro ao corrigir dados de upgrade', [
                    'usage_control_id' => $usageControl->id,
                    'error' => $e->getMessage()
                ]);
            }
        }
        
        $this->info("\n✅ Processamento concluído:");
        $this->info("   - Registros corrigidos: {$fixed}");
        $this->info("   - Erros encontrados: {$errors}");
        
        if ($dryRun) {
            $this->warn('⚠️  Esta foi apenas uma simulação. Execute sem --dry-run para aplicar as correções.');
        }
        
        return 0;
    }
    
    private function processUsageControl(UsageControl $usageControl, bool $dryRun, int &$fixed)
    {
        $subscription = $usageControl->subscription;
        $plan = $subscription->plan;
        
        $this->line("\n📋 Analisando UsageControl ID {$usageControl->id}:");
        $this->line("   - Empresa: {$usageControl->company_id}");
        $this->line("   - Plano: {$plan->name} (Limite: {$plan->budget_limit})");
        $this->line("   - Período: {$usageControl->month}/{$usageControl->year}");
        $this->line("   - Orçamentos usados: {$usageControl->budgets_used}");
        $this->line("   - Extras comprados: {$usageControl->extra_budgets_purchased}");
        $this->line("   - Extras usados: {$usageControl->extra_budgets_used}");
        $this->line("   - Herdados: {$usageControl->inherited_budgets}");
        $this->line("   - Herdados usados: {$usageControl->inherited_budgets_used}");
        
        $needsFix = false;
        $newData = [];
        
        // Se tem orçamentos extras, transferir para herdados (correção da lógica)
        if ($usageControl->extra_budgets_purchased > 0 && $usageControl->inherited_budgets == 0) {
            $needsFix = true;
            $newData['inherited_budgets'] = $usageControl->extra_budgets_purchased;
            $newData['inherited_budgets_used'] = $usageControl->extra_budgets_used;
            $newData['extra_budgets_purchased'] = 0;
            $newData['extra_budgets_used'] = 0;
            
            $this->info("   🔄 Transferindo {$usageControl->extra_budgets_purchased} orçamentos extras para herdados");
        }
        
        // Verificar se o uso está correto para o plano atual
        if ($usageControl->budgets_used > $plan->budget_limit && $plan->budget_limit > 0) {
            $needsFix = true;
            $excess = $usageControl->budgets_used - $plan->budget_limit;
            $newData['budgets_used'] = $plan->budget_limit;
            $newData['extra_budgets_used'] = ($newData['extra_budgets_used'] ?? $usageControl->extra_budgets_used) + $excess;
            
            $this->info("   🔄 Movendo {$excess} orçamentos excedentes para extras");
        }
        
        if ($needsFix) {
            if (!$dryRun) {
                DB::beginTransaction();
                try {
                    $usageControl->update($newData);
                    DB::commit();
                    
                    Log::info('Dados de upgrade corrigidos', [
                        'usage_control_id' => $usageControl->id,
                        'company_id' => $usageControl->company_id,
                        'old_data' => $usageControl->getOriginal(),
                        'new_data' => $newData
                    ]);
                } catch (\Exception $e) {
                    DB::rollBack();
                    throw $e;
                }
            }
            
            $fixed++;
            $this->info("   ✅ Registro " . ($dryRun ? 'seria corrigido' : 'corrigido com sucesso'));
        } else {
            $this->line("   ℹ️  Nenhuma correção necessária");
        }
    }
}
