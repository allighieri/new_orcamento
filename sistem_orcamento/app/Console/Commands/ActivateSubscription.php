<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Subscription;
use App\Models\UsageControl;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ActivateSubscription extends Command
{
    protected $signature = 'subscription:activate {subscription_id : ID da assinatura}';
    protected $description = 'Ativar uma assinatura manualmente';

    public function handle()
    {
        try {
            $subscriptionId = $this->argument('subscription_id');
            
            // Buscar assinatura
            $subscription = Subscription::find($subscriptionId);
            if (!$subscription) {
                $this->error("Assinatura com ID {$subscriptionId} não encontrada.");
                return 1;
            }

            // Verificar se já está ativa
            if ($subscription->status === 'active') {
                $this->info("Assinatura já está ativa.");
                return 0;
            }

            // Verificar se a empresa já tem outra assinatura ativa
            $activeSubscription = $subscription->company->subscriptions()
                ->where('status', 'active')
                ->where('id', '!=', $subscription->id)
                ->first();
                
            if ($activeSubscription) {
                $this->error("A empresa já possui uma assinatura ativa (ID: {$activeSubscription->id}).");
                $this->info("Cancele a assinatura ativa antes de ativar esta.");
                return 1;
            }

            DB::beginTransaction();

            // Ativar assinatura
            $subscription->update([
                'status' => 'active',
                'start_date' => now(),
                'starts_at' => now(),
                'ends_at' => $subscription->billing_cycle === 'yearly' 
                    ? now()->addYear() 
                    : now()->addMonth()
            ]);

            // Criar ou atualizar registro de pagamento se não existir
            if (!$subscription->payment_id) {
                $amount = $subscription->billing_cycle === 'yearly' 
                    ? $subscription->plan->yearly_price 
                    : $subscription->plan->monthly_price;
                    
                $payment = \App\Models\Payment::create([
                    'company_id' => $subscription->company_id,
                    'subscription_id' => $subscription->id,
                    'plan_id' => $subscription->plan_id,
                    'asaas_payment_id' => 'manual_activation_' . time() . '_' . $subscription->id,
                    'amount' => $amount,
                    'billing_type' => 'MANUAL',
                    'billing_cycle' => $subscription->billing_cycle,
                    'type' => 'subscription',
                    'status' => 'confirmed',
                    'due_date' => now(),
                    'confirmed_at' => now(),
                    'description' => "Ativação manual da assinatura {$subscription->plan->name} - {$subscription->company->fantasy_name}"
                ]);

                // Atualizar subscription com payment_id
                $subscription->update(['payment_id' => $payment->id]);
                
                $this->info("✅ Registro de pagamento criado (ID: {$payment->id})");
            } else {
                // Se já existe pagamento, apenas confirmar
                $payment = $subscription->payment;
                if ($payment && $payment->status !== 'confirmed') {
                    $payment->update([
                        'status' => 'confirmed',
                        'confirmed_at' => now()
                    ]);
                    $this->info("✅ Pagamento existente confirmado (ID: {$payment->id})");
                }
            }

            // Criar controle de uso para o mês atual
            $usageControl = UsageControl::getOrCreateForCurrentMonth(
                $subscription->company_id,
                $subscription->id
            );

            DB::commit();

            $this->info("✅ Assinatura ativada com sucesso!");
            $this->newLine();
            
            $this->table(
                ['Campo', 'Valor'],
                [
                    ['ID', $subscription->id],
                    ['Empresa', $subscription->company->name],
                    ['Plano', $subscription->plan->name],
                    ['Status', 'active'],
                    ['Data Ativação', now()->format('d/m/Y H:i')],
                    ['Controle de Uso', 'Criado para o mês atual']
                ]
            );
            
            if (isset($payment)) {
                $this->info("\n💳 Pagamento:");
                $this->info("   ID: {$payment->id}");
                $this->info("   Status: {$payment->status}");
                $this->info("   Valor: R$ " . number_format($payment->amount, 2, ',', '.'));
            }

            Log::info('Assinatura ativada manualmente', [
                'subscription_id' => $subscription->id,
                'company_id' => $subscription->company_id,
                'activated_by' => 'manual_command'
            ]);

            return 0;

        } catch (\Exception $e) {
            DB::rollBack();
            
            $this->error("Erro ao ativar assinatura: {$e->getMessage()}");
            
            Log::error('Erro ao ativar assinatura manual', [
                'subscription_id' => $subscriptionId ?? null,
                'error' => $e->getMessage()
            ]);
            
            return 1;
        }
    }
}