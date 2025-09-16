<?php

namespace App\Console\Commands;

use App\Models\Payment;
use App\Models\Subscription;
use App\Models\Plan;
use App\Models\Company;
use App\Models\UsageControl;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class TestWebhookPayment extends Command
{
    protected $signature = 'test:webhook-payment {payment_id} {--plan_id=2}';
    protected $description = 'Simula o processamento de webhook para testar atualização de assinatura';

    public function handle()
    {
        $paymentId = $this->argument('payment_id');
        $planId = $this->option('plan_id');
        
        $payment = Payment::find($paymentId);
        
        if (!$payment) {
            $this->error("Pagamento ID {$paymentId} não encontrado.");
            return 1;
        }
        
        $plan = Plan::find($planId);
        
        if (!$plan) {
            $this->error("Plano ID {$planId} não encontrado.");
            return 1;
        }
        
        $this->info("Testando processamento de webhook para pagamento ID: {$paymentId}");
        $this->info("Plano de destino: {$plan->name} (ID: {$plan->id})");
        
        try {
            DB::beginTransaction();
            
            // Atualizar status do pagamento
            $payment->update([
                'status' => 'RECEIVED',
                'paid_at' => now(),
                'updated_at' => now()
            ]);
            
            $this->info("✅ Status do pagamento atualizado para RECEIVED");
            
            // Buscar assinatura ativa da empresa
            $subscription = Subscription::where('company_id', $payment->company_id)
                ->where('status', 'active')
                ->first();
            
            if ($subscription) {
                $this->info("📋 Assinatura ativa encontrada: ID {$subscription->id}, Plano atual: {$subscription->plan->name}");
                
                // Cancelar assinatura atual
                $subscription->update([
                    'status' => 'cancelled',
                    'ends_at' => now(),
                    'updated_at' => now()
                ]);
                
                $this->info("❌ Assinatura atual cancelada");
            } else {
                $this->info("ℹ️ Nenhuma assinatura ativa encontrada");
            }
            
            // Criar nova assinatura
            $startDate = now();
            $endDate = $startDate->copy()->addMonth();
            $gracePeriodEndDate = $endDate->copy()->addDays(3); // 3 dias de período de graça
            
            $newSubscription = Subscription::create([
                'company_id' => $payment->company_id,
                'plan_id' => $plan->id,
                'status' => 'active',
                'start_date' => $startDate,
                'end_date' => $endDate,
                'starts_at' => $startDate,
                'ends_at' => $endDate,
                'grace_period_ends_at' => $gracePeriodEndDate,
                'remaining_budgets' => $plan->budget_limit ?? 5,
                'next_billing_date' => $endDate,
                'amount_paid' => $plan->price ?? 29.90,
                'billing_cycle' => 'monthly',
                'created_at' => now(),
                'updated_at' => now()
            ]);
            
            $this->info("✅ Nova assinatura criada: ID {$newSubscription->id}, Plano: {$plan->name}");
            
            // Resetar controle de uso
            $usageControl = UsageControl::where('company_id', $payment->company_id)->first();
            
            if ($usageControl) {
                $usageControl->update([
                    'budgets_used' => 0,
                    'budget_limit' => $plan->budget_limit,
                    'updated_at' => now()
                ]);
                
                $this->info("🔄 Controle de uso resetado: 0/{$plan->budget_limit} orçamentos");
            } else {
                UsageControl::create([
                    'company_id' => $payment->company_id,
                    'budgets_used' => 0,
                    'budget_limit' => $plan->budget_limit,
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
                
                $this->info("➕ Controle de uso criado: 0/{$plan->budget_limit} orçamentos");
            }
            
            DB::commit();
            
            $this->info("\n🎉 Processamento concluído com sucesso!");
            $this->info("📊 Verificando resultado...");
            
            // Verificar resultado
            $company = Company::find($payment->company_id);
            $activeSubscription = $company->activeSubscription();
            
            if ($activeSubscription) {
                $this->info("✅ Assinatura ativa: {$activeSubscription->plan->name} (ID: {$activeSubscription->id})");
                $this->info("📅 Período: {$activeSubscription->start_date->format('d/m/Y')} até {$activeSubscription->end_date->format('d/m/Y')}");
                $this->info("🎯 Limite de orçamentos: {$activeSubscription->plan->budget_limit}");
            } else {
                $this->error("❌ Nenhuma assinatura ativa encontrada após processamento!");
            }
            
            return 0;
            
        } catch (\Exception $e) {
            DB::rollBack();
            $this->error("Erro ao processar webhook: " . $e->getMessage());
            Log::error('Erro no teste de webhook', [
                'payment_id' => $paymentId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return 1;
        }
    }
}
