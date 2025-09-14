<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Carbon\Carbon;

class Subscription extends Model
{
    protected $fillable = [
        'company_id',
        'plan_id',
        'billing_cycle',
        'status',
        'start_date',
        'end_date',
        'next_billing_date',
        'amount_paid',
        'grace_period_days',
        'in_grace_period',
        'asaas_subscription_id',
        'can_downgrade_to_monthly',
        'cancellation_fee_paid',
        'cancelled_at'
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'next_billing_date' => 'date',
        'amount_paid' => 'decimal:2',
        'in_grace_period' => 'boolean',
        'can_downgrade_to_monthly' => 'boolean',
        'cancellation_fee_paid' => 'boolean',
        'cancelled_at' => 'datetime'
    ];

    /**
     * Relacionamento com empresa
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * Relacionamento com plano
     */
    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class);
    }

    /**
     * Relacionamento com controles de uso
     */
    public function usageControls(): HasMany
    {
        return $this->hasMany(UsageControl::class);
    }

    /**
     * Verifica se a assinatura está ativa
     */
    public function isActive(): bool
    {
        return $this->status === 'active' && $this->end_date >= now();
    }

    /**
     * Verifica se está no período de carência
     */
    public function isInGracePeriod(): bool
    {
        return $this->in_grace_period && 
               $this->end_date < now() && 
               $this->end_date->addDays($this->grace_period_days) >= now();
    }

    /**
     * Verifica se a assinatura expirou completamente
     */
    public function isExpired(): bool
    {
        $graceEndDate = $this->end_date->addDays($this->grace_period_days);
        return now() > $graceEndDate;
    }

    /**
     * Calcula o valor de cancelamento antecipado (50% do valor restante)
     * Fórmula: (valor_total_anual - valor_já_pago) / 2
     */
    public function getCancellationFee(): float
    {
        if ($this->billing_cycle !== 'annual') {
            return 0; // Planos mensais não têm taxa de cancelamento
        }

        if ($this->cancellation_fee_paid) {
            return 0; // Taxa já foi paga
        }

        // Se não tem assinatura no Asaas (pagamento único), não há taxa de cancelamento
        if (is_null($this->asaas_subscription_id)) {
            return 0;
        }

        // Para planos anuais com pagamento único, calcular baseado no tempo decorrido
        $monthsElapsed = $this->start_date->diffInMonths(now());
        $monthsElapsed = min($monthsElapsed, 12); // Máximo 12 meses
        
        // Para planos anuais, usar o preço anual total
        $monthlyPrice = $this->plan->annual_price; // Preço mensal do plano anual (R$ 45)
        $totalAnnualValue = $monthlyPrice * 12; // Valor total anual (12 meses)
        $valueUsed = $monthlyPrice * $monthsElapsed; // Valor proporcional usado
        $remainingValue = $totalAnnualValue - $valueUsed; // Valor restante
        
        return $remainingValue / 2; // 50% do valor restante
    }

    /**
     * Verifica se pode fazer downgrade para mensal
     */
    public function canDowngradeToMonthly(): bool
    {
        if ($this->billing_cycle !== 'annual') {
            return true; // Planos mensais podem mudar livremente
        }

        // Se não tem assinatura no Asaas (pagamento único), pode trocar livremente
        if (is_null($this->asaas_subscription_id)) {
            return true;
        }

        // Só pode fazer downgrade se pagou a taxa de cancelamento ou se passou 12 meses
        return $this->cancellation_fee_paid || 
               $this->start_date->diffInMonths(now()) >= 12;
    }

    /**
     * Verifica se é um plano anual
     */
    public function isAnnual(): bool
    {
        return $this->billing_cycle === 'annual';
    }

    /**
     * Calcula quantos meses restam no plano anual
     */
    public function getMonthsRemaining(): int
    {
        if (!$this->isAnnual()) {
            return 0;
        }

        return max(0, now()->diffInMonths($this->end_date, false));
    }

    /**
     * Verifica se precisa de renovação em breve (3 dias)
     */
    public function needsRenewalWarning(): bool
    {
        return $this->end_date->diffInDays(now()) <= 3 && $this->isActive();
    }
}
