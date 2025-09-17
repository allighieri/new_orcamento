<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UsageControl extends Model
{
    protected $fillable = [
        'company_id',
        'subscription_id',
        'year',
        'month',
        'budgets_used',
        'extra_budgets_purchased',
        'extra_budgets_used',
        'inherited_budgets',
        'inherited_budgets_used'
    ];

    /**
     * Relacionamento com empresa
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * Relacionamento com assinatura
     */
    public function subscription(): BelongsTo
    {
        return $this->belongsTo(Subscription::class);
    }

    /**
     * Verifica se ainda pode criar orçamentos
     */
    public function canCreateBudget(): bool
    {
        $planLimit = $this->subscription->plan->budget_limit;
        
        // Se o plano tem limite ilimitado (null ou 0), sempre pode criar
        if ($this->subscription->plan->isUnlimited()) {
            return true;
        }
        
        $totalAvailable = $planLimit + $this->extra_budgets_purchased + $this->inherited_budgets;
        $totalUsed = $this->budgets_used + $this->extra_budgets_used + $this->inherited_budgets_used;
        
        return $totalUsed < $totalAvailable;
    }

    /**
     * Incrementa o uso de orçamentos (prioriza plano base, depois extras, depois herdados)
     */
    public function incrementUsage(): void
    {
        $planLimit = $this->subscription->plan->budget_limit;
        
        // Se ainda tem orçamentos do plano base (apenas para planos limitados)
        if (!$this->subscription->plan->isUnlimited() && $this->budgets_used < $planLimit) {
            $this->increment('budgets_used');
        }
        // Se tem orçamentos extras disponíveis
        elseif ($this->extra_budgets_used < $this->extra_budgets_purchased) {
            $this->increment('extra_budgets_used');
        }
        // Se tem orçamentos herdados disponíveis
        elseif ($this->inherited_budgets_used < $this->inherited_budgets) {
            $this->increment('inherited_budgets_used');
        }
    }

    /**
     * Retorna quantos orçamentos restam
     */
    public function getRemainingBudgets(): int
    {
        $planLimit = $this->subscription->plan->budget_limit;
        
        // Se o plano tem limite ilimitado (null ou 0)
        if ($this->subscription->plan->isUnlimited()) {
            return PHP_INT_MAX;
        }
        
        $totalAvailable = $planLimit + $this->extra_budgets_purchased + $this->inherited_budgets;
        $totalUsed = $this->budgets_used + $this->extra_budgets_used + $this->inherited_budgets_used;
        
        return max(0, $totalAvailable - $totalUsed);
    }

    /**
     * Verifica se precisa comprar mais orçamentos
     */
    public function needsMoreBudgets(): bool
    {
        $planLimit = $this->subscription->plan->budget_limit;
        
        // Se o plano tem limite ilimitado (null ou 0)
        if ($this->subscription->plan->isUnlimited()) {
            return false;
        }
        
        return $this->getRemainingBudgets() === 0;
    }

    /**
     * Adiciona orçamentos extras
     */
    public function addExtraBudgets(int $quantity): void
    {
        $this->extra_budgets_purchased += $quantity;
        $this->save();
    }

    /**
     * Adiciona orçamentos herdados de upgrade
     */
    public function addInheritedBudgets(int $quantity): void
    {
        $this->inherited_budgets += $quantity;
        $this->save();
    }

    /**
     * Cria ou obtém o controle de uso para o mês atual
     */
    public static function getOrCreateForCurrentMonth(int $companyId, int $subscriptionId): self
    {
        $year = now()->year;
        $month = now()->month;

        // Primeiro tenta encontrar o registro existente
        $usageControl = self::where('company_id', $companyId)
            ->where('year', $year)
            ->where('month', $month)
            ->first();

        if ($usageControl) {
            // Se existe, apenas atualiza a subscription_id
            $usageControl->update(['subscription_id' => $subscriptionId]);
            return $usageControl;
        }

        // Se não existe, cria um novo com valores padrão
        return self::create([
            'company_id' => $companyId,
            'subscription_id' => $subscriptionId,
            'year' => $year,
            'month' => $month,
            'budgets_used' => 0,
            'extra_budgets_purchased' => 0,
            'extra_budgets_used' => 0,
            'inherited_budgets' => 0,
            'inherited_budgets_used' => 0
        ]);
    }

    /**
     * Cria ou obtém o controle de uso para o mês atual, resetando valores se necessário
     * Usado especificamente para mudanças de plano
     */
    public static function getOrCreateForCurrentMonthWithReset(int $companyId, int $subscriptionId, int $inheritedBudgets = 0): self
    {
        $year = now()->year;
        $month = now()->month;

        // Primeiro tenta encontrar o registro existente
        $usageControl = self::where('company_id', $companyId)
            ->where('year', $year)
            ->where('month', $month)
            ->first();

        if ($usageControl) {
            // Se existe, reseta todos os valores para a nova assinatura
            $usageControl->update([
                'subscription_id' => $subscriptionId,
                'budgets_used' => 0,
                'extra_budgets_purchased' => 0,
                'extra_budgets_used' => 0,
                'inherited_budgets' => $inheritedBudgets,
                'inherited_budgets_used' => 0
            ]);
            return $usageControl;
        }

        // Se não existe, cria um novo com valores padrão
        return self::create([
            'company_id' => $companyId,
            'subscription_id' => $subscriptionId,
            'year' => $year,
            'month' => $month,
            'budgets_used' => 0,
            'extra_budgets_purchased' => 0,
            'extra_budgets_used' => 0,
            'inherited_budgets' => $inheritedBudgets,
            'inherited_budgets_used' => 0
        ]);
    }
}
