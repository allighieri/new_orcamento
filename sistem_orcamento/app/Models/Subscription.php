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
        'in_grace_period'
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'next_billing_date' => 'date',
        'amount_paid' => 'decimal:2',
        'in_grace_period' => 'boolean'
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
     * Calcula o valor de cancelamento antecipado (50% dos meses restantes)
     */
    public function getCancellationFee(): float
    {
        if ($this->billing_cycle !== 'annual') {
            return 0; // Planos mensais não têm taxa de cancelamento
        }

        $monthsRemaining = now()->diffInMonths($this->end_date);
        $monthlyPrice = $this->plan->annual_price;
        
        return ($monthsRemaining * $monthlyPrice) * 0.5;
    }

    /**
     * Verifica se precisa de renovação em breve (3 dias)
     */
    public function needsRenewalWarning(): bool
    {
        return $this->end_date->diffInDays(now()) <= 3 && $this->isActive();
    }
}
