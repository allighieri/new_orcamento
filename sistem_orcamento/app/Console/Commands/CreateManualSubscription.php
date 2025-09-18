<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Subscription;
use App\Models\Plan;
use App\Models\Company;
use App\Models\UsageControl;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class CreateManualSubscription extends Command
{
    protected $signature = 'subscription:create-manual 
                            {company_id : ID da empresa}
                            {plan_id : ID do plano}
                            {--cycle=monthly : Ciclo de cobrança (monthly ou yearly)}
                            {--status=active : Status da assinatura (pending, active, cancelled)}
                            {--start-date= : Data de início (formato: Y-m-d)}
                            {--end-date= : Data de fim (formato: Y-m-d)}
                            {--auto-renew=true : Renovação automática (true ou false)}
                            {--force-replace : Cancelar automaticamente assinaturas ativas anteriores}';
    
    protected $description = 'Criar uma assinatura manualmente sem depender do Asaas ou webhook';

    public function handle()
    {
        try {
            // Validar parâmetros
            $companyId = $this->argument('company_id');
            $planId = $this->argument('plan_id');
            $cycle = $this->option('cycle');
            $status = $this->option('status');
            $startDate = $this->option('start-date');
            $endDate = $this->option('end-date');
            $autoRenew = $this->option('auto-renew') === 'true';

            // Validar empresa
            $company = Company::find($companyId);
            if (!$company) {
                $this->error("Empresa com ID {$companyId} não encontrada.");
                return 1;
            }

            // Validar plano
            $plan = Plan::find($planId);
            if (!$plan) {
                $this->error("Plano com ID {$planId} não encontrado.");
                return 1;
            }

            // Validar ciclo
            if (!in_array($cycle, ['monthly', 'yearly'])) {
                $this->error("Ciclo deve ser 'monthly' ou 'yearly'.");
                return 1;
            }

            // Validar status
            if (!in_array($status, ['pending', 'active', 'cancelled', 'expired'])) {
                $this->error("Status deve ser 'pending', 'active', 'cancelled' ou 'expired'.");
                return 1;
            }

            // Verificar se já tem assinatura ativa
            $activeSubscription = $company->subscriptions()->where('status', 'active')->first();
            if ($activeSubscription && $status === 'active') {
                if ($this->option('force-replace')) {
                    // Cancelar assinatura ativa anterior automaticamente
                    $activeSubscription->update([
                        'status' => 'cancelled',
                        'cancelled_at' => now(),
                        'cancellation_reason' => 'Substituída por nova assinatura manual'
                    ]);
                    
                    // Cancelar pagamento associado se existir
                    if ($activeSubscription->payment_id) {
                        $activeSubscription->payment()->update(['status' => 'cancelled']);
                    }
                    
                    $this->info("✅ Assinatura anterior (ID: {$activeSubscription->id}) cancelada automaticamente.");
                } else {
                    $this->error("Empresa já possui uma assinatura ativa (ID: {$activeSubscription->id}).");
                    $this->info("Para criar uma nova assinatura ativa, use --force-replace ou cancele a atual manualmente.");
                    return 1;
                }
            }

            // Definir datas
            $startDateTime = $startDate ? Carbon::parse($startDate) : now();
            
            if ($endDate) {
                $endDateTime = Carbon::parse($endDate);
            } else {
                $endDateTime = $cycle === 'yearly' 
                    ? $startDateTime->copy()->addYear() 
                    : $startDateTime->copy()->addMonth();
            }

            $gracePeriodEndDate = $endDateTime->copy()->addDays(3);

            DB::beginTransaction();

            // Criar assinatura
            $subscription = Subscription::create([
                'company_id' => $company->id,
                'plan_id' => $plan->id,
                'billing_cycle' => $cycle,
                'status' => $status,
                'start_date' => $startDateTime,
                'end_date' => $endDateTime,
                'starts_at' => $startDateTime,
                'ends_at' => $endDateTime,
                'grace_period_ends_at' => $gracePeriodEndDate,
                'auto_renew' => $autoRenew,
                'asaas_subscription_id' => null, // Manual, sem Asaas
            ]);

            // Criar registro de pagamento para a assinatura
            $amount = $cycle === 'yearly' ? $plan->yearly_price : $plan->monthly_price;
            $payment = \App\Models\Payment::create([
                'company_id' => $companyId,
                'subscription_id' => $subscription->id,
                'plan_id' => $plan->id,
                'asaas_payment_id' => 'manual_' . time() . '_' . $subscription->id,
                'amount' => $amount,
                'billing_type' => 'MANUAL',
                'billing_cycle' => $cycle,
                'type' => 'subscription',
                'status' => $status === 'active' ? 'confirmed' : 'pending',
                'due_date' => now(),
                'confirmed_at' => $status === 'active' ? now() : null,
                'description' => "Assinatura manual do plano {$plan->name} - {$company->fantasy_name}"
            ]);

            // Atualizar subscription com payment_id
            $subscription->update(['payment_id' => $payment->id]);

            // Criar controle de uso para o mês atual se a assinatura estiver ativa
            if ($status === 'active') {
                $usageControl = \App\Models\UsageControl::getOrCreateForCurrentMonthWithReset(
                    $companyId,
                    $subscription->id,
                    0 // Sempre começar com 0 orçamentos herdados para assinaturas manuais
                );

                $this->info("✅ Controle de uso criado para o mês atual");
            }

            DB::commit();

            // Exibir informações da assinatura criada
            $this->info("✅ Assinatura criada com sucesso!");
            $this->newLine();
            
$this->info("\n📋 Detalhes da Assinatura:");
            $this->info("   ID: {$subscription->id}");
            $this->info("   Empresa: {$company->name} (ID: {$company->id})");
            $this->info("   Plano: {$plan->name} (ID: {$plan->id})");
            $this->info("   Ciclo: {$cycle}");
            $this->info("   Status: {$status}");
            $this->info("   Início: {$subscription->starts_at->format('d/m/Y H:i')}");
            $this->info("   Término: {$subscription->ends_at->format('d/m/Y H:i')}");
            $this->info("   Valor: R$ " . number_format($amount, 2, ',', '.'));
            $this->info("\n💳 Pagamento Criado:");
            $this->info("   ID: {$payment->id}");
            $this->info("   Status: {$payment->status}");
            $this->info("   Valor: R$ " . number_format($payment->amount, 2, ',', '.'));

            $this->newLine();
            $this->info("💡 Dicas:");
            $this->line("   • Para ativar: php artisan subscription:activate {$subscription->id}");
            $this->line("   • Para cancelar: php artisan subscription:cancel {$subscription->id}");
            $this->line("   • Para verificar: php artisan check:subscriptions {$company->id}");

            Log::info('Assinatura manual criada', [
                'subscription_id' => $subscription->id,
                'company_id' => $company->id,
                'plan_id' => $plan->id,
                'status' => $status,
                'created_by' => 'manual_command'
            ]);

            return 0;

        } catch (\Exception $e) {
            DB::rollBack();
            
            $this->error("Erro ao criar assinatura: {$e->getMessage()}");
            
            Log::error('Erro ao criar assinatura manual', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return 1;
        }
    }

    /**
     * Exibir ajuda com exemplos
     */
    public function getHelp(): string
    {
        return parent::getHelp() . "\n\n" .
            "Exemplos de uso:\n" .
            "  php artisan subscription:create-manual 1 2\n" .
            "  php artisan subscription:create-manual 1 2 --cycle=yearly\n" .
            "  php artisan subscription:create-manual 1 2 --status=pending --start-date=2024-01-01\n" .
            "  php artisan subscription:create-manual 1 2 --end-date=2024-12-31 --auto-renew=false\n" .
            "  php artisan subscription:create-manual 1 3 --cycle=yearly --force-replace\n" .
            "\n" .
            "Opções especiais:\n" .
            "  --force-replace: Cancela automaticamente assinaturas ativas anteriores\n";
    }
}