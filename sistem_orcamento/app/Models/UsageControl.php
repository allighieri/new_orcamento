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
        'budgets_limit',
        'extra_budgets_purchased',
        'extra_amount_paid'
    ];

    protected $casts = [
        'extra_amount_paid' => 'decimal:2'
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
        $totalLimit = $this->budgets_limit + $this->extra_budgets_purchased;
        return $this->budgets_used < $totalLimit;
    }

    /**
     * Incrementa o uso de orçamentos
     */
    public function incrementUsage(): void
    {
        $this->increment('budgets_used');
    }

    /**
     * Retorna quantos orçamentos restam
     */
    public function getRemainingBudgets(): int
    {
        $totalLimit = $this->budgets_limit + $this->extra_budgets_purchased;
        return max(0, $totalLimit - $this->budgets_used);
    }

    /**
     * Verifica se precisa comprar mais orçamentos
     */
    public function needsMoreBudgets(): bool
    {
        return $this->getRemainingBudgets() === 0;
    }

    /**
     * Adiciona orçamentos extras
     */
    public function addExtraBudgets(int $quantity, float $amount): void
    {
        $this->extra_budgets_purchased += $quantity;
        $this->extra_amount_paid += $amount;
        $this->save();
    }

    /**
     * Cria ou obtém o controle de uso para o mês atual
     */
    public static function getOrCreateForCurrentMonth(int $companyId, int $subscriptionId, int $budgetLimit): self
    {
        $year = now()->year;
        $month = now()->month;

        // Primeiro tenta encontrar o registro existente
        $usageControl = self::where('company_id', $companyId)
            ->where('year', $year)
            ->where('month', $month)
            ->first();

        if ($usageControl) {
            // Se existe, apenas atualiza os campos que podem ter mudado
            $usageControl->update([
                'subscription_id' => $subscriptionId,
                'budgets_limit' => $budgetLimit
            ]);
            return $usageControl;
        }

        // Se não existe, cria um novo com valores padrão
        return self::create([
            'company_id' => $companyId,
            'year' => $year,
            'month' => $month,
            'subscription_id' => $subscriptionId,
            'budgets_used' => 0,
            'budgets_limit' => $budgetLimit,
            'extra_budgets_purchased' => 0,
            'extra_amount_paid' => 0
        ]);
    }
}
