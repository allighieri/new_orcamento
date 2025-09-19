<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Subscription;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class CheckExpiredSubscriptions extends Command
{
    protected $signature = 'subscriptions:check-expired {--dry-run : Apenas mostrar quais assinaturas seriam canceladas}';
    protected $description = 'Verificar e cancelar assinaturas que passaram do período de graça (3 dias após vencimento)';

    public function handle()
    {
        $dryRun = $this->option('dry-run');
        
        $this->info('🔍 Verificando assinaturas vencidas...');
        
        // Buscar assinaturas ativas que já passaram do período de graça
        $expiredSubscriptions = Subscription::where('status', 'active')
            ->where('ends_at', '<', now())
            ->where('grace_period_ends_at', '<', now())
            ->get();
            
        if ($expiredSubscriptions->isEmpty()) {
            $this->info('✅ Nenhuma assinatura vencida encontrada.');
            return 0;
        }
        
        $this->info("📋 Encontradas {$expiredSubscriptions->count()} assinatura(s) vencida(s):");
        
        foreach ($expiredSubscriptions as $subscription) {
            $company = $subscription->company;
            $plan = $subscription->plan;
            
            $this->line("   • ID: {$subscription->id} | Empresa: {$company->name} | Plano: {$plan->name}");
            $this->line("     Venceu em: {$subscription->ends_at->format('d/m/Y H:i')}");
            $this->line("     Período de graça terminou em: {$subscription->grace_period_ends_at->format('d/m/Y H:i')}");
            
            if (!$dryRun) {
                try {
                    // Cancelar a assinatura
                    $subscription->update([
                        'status' => 'cancelled',
                        'cancelled_at' => now(),
                        'cancellation_reason' => 'Cancelamento automático - período de graça expirado'
                    ]);
                    
                    // Cancelar o pagamento associado se existir
                    if ($subscription->payment_id) {
                        $subscription->payment()->update([
                            'status' => 'cancelled'
                        ]);
                    }
                    
                    $this->info("     ✅ Assinatura cancelada automaticamente");
                    
                    Log::info('Assinatura cancelada automaticamente por vencimento', [
                        'subscription_id' => $subscription->id,
                        'company_id' => $subscription->company_id,
                        'company_name' => $company->name,
                        'plan_name' => $plan->name,
                        'ended_at' => $subscription->ends_at,
                        'grace_period_ended_at' => $subscription->grace_period_ends_at,
                        'cancelled_at' => now(),
                        'reason' => 'Cancelamento automático - período de graça expirado'
                    ]);
                    
                } catch (\Exception $e) {
                    $this->error("     ❌ Erro ao cancelar assinatura: {$e->getMessage()}");
                    
                    Log::error('Erro ao cancelar assinatura automaticamente', [
                        'subscription_id' => $subscription->id,
                        'error' => $e->getMessage()
                    ]);
                }
            } else {
                $this->line("     🔄 [DRY RUN] Seria cancelada automaticamente");
            }
            
            $this->line('');
        }
        
        if ($dryRun) {
            $this->info('🔄 Modo dry-run ativo. Nenhuma alteração foi feita.');
            $this->info('💡 Execute sem --dry-run para cancelar as assinaturas vencidas.');
        } else {
            $this->info("✅ Processamento concluído. {$expiredSubscriptions->count()} assinatura(s) cancelada(s).");
        }
        
        return 0;
    }
}