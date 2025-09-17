<?php

namespace App\Services;

use App\Models\Plan;
use App\Models\Subscription;
use App\Models\UsageControl;
use App\Models\Payment;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class PlanUpgradeService
{
    /**
     * Processa upgrade de plano com herança de orçamentos
     */
    public function processUpgrade(Subscription $oldSubscription, Plan $newPlan, Payment $payment): Subscription
    {
        try {
            DB::beginTransaction();

            // Obter controle de uso atual
            $currentUsageControl = UsageControl::getOrCreateForCurrentMonth(
                $oldSubscription->company_id,
                $oldSubscription->id
            );

            // Calcular orçamentos herdados
            $inheritedBudgets = $this->calculateInheritedBudgets($oldSubscription, $newPlan, $currentUsageControl);

            // Cancelar assinatura antiga
            $oldSubscription->update([
                'status' => 'cancelled',
                'cancelled_at' => now(),
                'end_date' => now()
            ]);

            // Criar nova assinatura
            $startDate = now();
            $endDate = $this->calculateEndDate($payment->billing_cycle ?? 'monthly');
            $gracePeriodEndDate = $endDate->copy()->addDays(3); // 3 dias de período de graça
            
            // Converter billing_cycle para o formato correto da tabela subscriptions
            $billingCycleForSubscription = $payment->billing_cycle === 'annual' ? 'yearly' : ($payment->billing_cycle ?? 'monthly');
            
            $newSubscription = Subscription::create([
                'company_id' => $oldSubscription->company_id,
                'plan_id' => $newPlan->id,
                'billing_cycle' => $billingCycleForSubscription,
                'status' => 'active',
                'start_date' => $startDate,
                'end_date' => $endDate,
                'starts_at' => $startDate,
                'ends_at' => $endDate,
                'grace_period_ends_at' => $gracePeriodEndDate,

                'next_billing_date' => $this->calculateNextBillingDate($payment->billing_cycle ?? 'monthly'),
                'auto_renew' => true
            ]);

            // Atualizar pagamento com nova assinatura
            $payment->update([
                'subscription_id' => $newSubscription->id
            ]);

            // Criar ou atualizar controle de uso para nova assinatura com orçamentos herdados
            // Usa método especial que reseta todos os valores para evitar manter dados antigos
            // Para planos ilimitados (como Ouro), sempre zera todos os campos
            $finalInheritedBudgets = $newPlan->isUnlimited() ? 0 : $inheritedBudgets;
            $newUsageControl = UsageControl::getOrCreateForCurrentMonthWithReset(
                $oldSubscription->company_id,
                $newSubscription->id,
                $finalInheritedBudgets
            );

            DB::commit();

            Log::info('Upgrade de plano processado com sucesso', [
                'company_id' => $oldSubscription->company_id,
                'old_subscription_id' => $oldSubscription->id,
                'new_subscription_id' => $newSubscription->id,
                'old_plan_id' => $oldSubscription->plan_id,
                'new_plan_id' => $newPlan->id,
                'inherited_budgets' => $inheritedBudgets
            ]);

            return $newSubscription;

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erro ao processar upgrade de plano', [
                'error' => $e->getMessage(),
                'old_subscription_id' => $oldSubscription->id,
                'new_plan_id' => $newPlan->id
            ]);
            throw $e;
        }
    }

    /**
     * Calcula orçamentos herdados do plano anterior
     */
    private function calculateInheritedBudgets(Subscription $oldSubscription, Plan $newPlan, UsageControl $currentUsageControl): int
    {
        $oldPlan = $oldSubscription->plan;
        
        // Se o plano antigo era ilimitado, não herda nada
        if ($oldPlan->isUnlimited()) {
            return 0;
        }

        // Se o novo plano é ilimitado, não precisa herdar
        if ($newPlan->isUnlimited()) {
            return 0;
        }

        // Removido: regra que zerava orçamentos herdados ao mudar para Ouro
        // Agora todos os orçamentos não utilizados são herdados independente do plano

        // Calcular orçamentos restantes do plano anterior
        $remainingFromPlan = max(0, $oldPlan->budget_limit - $currentUsageControl->budgets_used);
        $remainingFromExtras = max(0, $currentUsageControl->extra_budgets_purchased - $currentUsageControl->extra_budgets_used);
        $remainingFromInherited = max(0, $currentUsageControl->inherited_budgets - $currentUsageControl->inherited_budgets_used);

        $totalRemaining = $remainingFromPlan + $remainingFromExtras + $remainingFromInherited;

        // Herdar 100% dos orçamentos não utilizados, independente do tipo de mudança
        // O cliente já pagou por esses orçamentos e deve poder utilizá-los
        return $totalRemaining;
    }

    /**
     * Verifica se é um upgrade (plano mais caro)
     */
    private function isUpgrade(Plan $oldPlan, Plan $newPlan): bool
    {
        return $newPlan->monthly_price > $oldPlan->monthly_price;
    }

    /**
     * Verifica se é um downgrade (plano mais barato)
     */
    private function isDowngrade(Plan $oldPlan, Plan $newPlan): bool
    {
        return $newPlan->monthly_price < $oldPlan->monthly_price;
    }

    /**
     * Processa compra de orçamentos extras
     */
    public function processExtraBudgetsPurchase(Subscription $subscription, int $quantity, Payment $payment): void
    {
        try {
            DB::beginTransaction();

            // Obter controle de uso atual
            $usageControl = UsageControl::getOrCreateForCurrentMonth(
                $subscription->company_id,
                $subscription->id
            );

            // Adicionar orçamentos extras
            $usageControl->increment('extra_budgets_purchased', $quantity);

            // Atualizar pagamento
            $payment->update([
                'subscription_id' => $subscription->id
            ]);

            DB::commit();

            Log::info('Orçamentos extras adicionados com sucesso', [
                'company_id' => $subscription->company_id,
                'subscription_id' => $subscription->id,
                'quantity' => $quantity,
                'payment_id' => $payment->id
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erro ao processar compra de orçamentos extras', [
                'error' => $e->getMessage(),
                'subscription_id' => $subscription->id,
                'quantity' => $quantity
            ]);
            throw $e;
        }
    }

    /**
     * Calcula data de término da assinatura
     */
    private function calculateEndDate(string $billingCycle): Carbon
    {
        return match($billingCycle) {
            'yearly', 'annual' => now()->addYear(),
            'monthly' => now()->addMonth(),
            default => now()->addMonth()
        };
    }

    /**
     * Calcula próxima data de cobrança
     */
    private function calculateNextBillingDate(string $billingCycle): Carbon
    {
        return match($billingCycle) {
            'yearly', 'annual' => now()->addYear(),
            'monthly' => now()->addMonth(),
            default => now()->addMonth()
        };
    }

    /**
     * Obtém estatísticas de uso para uma empresa
     */
    public function getUsageStats(int $companyId): array
    {
        $subscription = Subscription::where('company_id', $companyId)
            ->where('status', 'active')
            ->with('plan')
            ->first();

        if (!$subscription) {
            return [
                'has_active_subscription' => false,
                'plan_name' => null,
                'budget_limit' => 0,
                'budgets_used' => 0,
                'extra_budgets_purchased' => 0,
                'extra_budgets_used' => 0,
                'inherited_budgets' => 0,
                'inherited_budgets_used' => 0,

                'can_create_budget' => false
            ];
        }

        $usageControl = UsageControl::getOrCreateForCurrentMonth(
            $companyId,
            $subscription->id
        );

        return [
            'has_active_subscription' => true,
            'plan_name' => $subscription->plan->name,
            'budget_limit' => $subscription->plan->budget_limit,
            'budgets_used' => $usageControl->budgets_used,
            'extra_budgets_purchased' => $usageControl->extra_budgets_purchased,
            'extra_budgets_used' => $usageControl->extra_budgets_used,
            'inherited_budgets' => $usageControl->inherited_budgets,
            'inherited_budgets_used' => $usageControl->inherited_budgets_used,

            'can_create_budget' => $usageControl->canCreateBudget()
        ];
    }
}
