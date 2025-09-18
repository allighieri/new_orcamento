<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Subscription;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CancelSubscription extends Command
{
    protected $signature = 'subscription:cancel {subscription_id : ID da assinatura} {--reason= : Motivo do cancelamento}';
    protected $description = 'Cancelar uma assinatura manualmente';

    public function handle()
    {
        try {
            $subscriptionId = $this->argument('subscription_id');
            $reason = $this->option('reason') ?? 'Cancelamento manual via comando';
            
            // Buscar assinatura
            $subscription = Subscription::find($subscriptionId);
            if (!$subscription) {
                $this->error("Assinatura com ID {$subscriptionId} não encontrada.");
                return 1;
            }

            // Verificar se já está cancelada
            if ($subscription->status === 'cancelled') {
                $this->info("Assinatura já está cancelada.");
                return 0;
            }

            // Confirmar cancelamento
            $this->info("Dados da assinatura a ser cancelada:");
            $this->table(
                ['Campo', 'Valor'],
                [
                    ['ID', $subscription->id],
                    ['Empresa', $subscription->company->name],
                    ['Plano', $subscription->plan->name],
                    ['Status Atual', $subscription->status],
                    ['Ciclo', $subscription->billing_cycle],
                    ['Data Início', $subscription->start_date?->format('d/m/Y H:i') ?? 'N/A'],
                    ['Data Fim', $subscription->end_date?->format('d/m/Y H:i') ?? 'N/A']
                ]
            );

            if (!$this->confirm('Deseja realmente cancelar esta assinatura?')) {
                $this->info('Cancelamento abortado.');
                return 0;
            }

            DB::beginTransaction();

            // Cancelar assinatura
            $subscription->update([
                'status' => 'cancelled',
                'cancelled_at' => now(),
                'cancellation_reason' => $reason
            ]);

            // Atualizar status do pagamento se existir
            if ($subscription->payment_id) {
                $payment = $subscription->payment;
                if ($payment && $payment->status !== 'cancelled') {
                    $payment->update([
                        'status' => 'cancelled'
                    ]);
                    $this->info("✅ Pagamento cancelado (ID: {$payment->id})");
                }
            }

            DB::commit();

            $this->info("✅ Assinatura cancelada com sucesso!");
            $this->newLine();
            
            $this->table(
                ['Campo', 'Valor'],
                [
                    ['ID', $subscription->id],
                    ['Status', 'cancelled'],
                    ['Data Cancelamento', now()->format('d/m/Y H:i')],
                    ['Motivo', $reason]
                ]
            );
            
            if (isset($payment)) {
                $this->newLine();
                $this->info("💳 Pagamento:");
                $this->info("   ID: {$payment->id}");
                $this->info("   Status: {$payment->status}");
            }

            $this->newLine();
            $this->info("💡 Nota: A empresa ainda pode usar o sistema até o fim do período de graça.");
            if ($subscription->grace_period_ends_at) {
                $this->line("   Período de graça termina em: {$subscription->grace_period_ends_at->format('d/m/Y H:i')}");
            }

            Log::info('Assinatura cancelada manualmente', [
                'subscription_id' => $subscription->id,
                'company_id' => $subscription->company_id,
                'reason' => $reason,
                'cancelled_by' => 'manual_command'
            ]);

            return 0;

        } catch (\Exception $e) {
            DB::rollBack();
            
            $this->error("Erro ao cancelar assinatura: {$e->getMessage()}");
            
            Log::error('Erro ao cancelar assinatura manual', [
                'subscription_id' => $subscriptionId ?? null,
                'error' => $e->getMessage()
            ]);
            
            return 1;
        }
    }
}