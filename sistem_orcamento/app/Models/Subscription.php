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
        'asaas_subscription_id',
        'billing_cycle',
        'status',
        'starts_at',
        'ends_at',
        'start_date',
        'end_date',
        'next_billing_date',
        'amount_paid',
        'payment_id',
        'grace_period_ends_at',
        'auto_renew'
    ];

    protected $casts = [
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
        'start_date' => 'datetime',
        'end_date' => 'datetime',
        'next_billing_date' => 'datetime',
        'amount_paid' => 'decimal:2',
        'grace_period_ends_at' => 'datetime',
        'auto_renew' => 'boolean'
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
     * Relacionamento com pagamento
     */
    public function payment(): BelongsTo
    {
        return $this->belongsTo(Payment::class);
    }

    /**
     * Verifica se a assinatura está ativa
     */
    public function isActive(): bool
    {
        return $this->status === 'active' && $this->ends_at >= now();
    }

    /**
     * Verifica se está no período de carência
     */
    public function isInGracePeriod(): bool
    {
        return $this->status === 'expired' && 
               $this->ends_at < now() && 
               $this->grace_period_ends_at >= now();
    }

    /**
     * Verifica se a assinatura expirou completamente
     */
    public function isExpired(): bool
    {
        return $this->grace_period_ends_at && now() > $this->grace_period_ends_at;
    }

    /**
     * Verifica se é um plano anual
     */
    public function isYearly(): bool
    {
        return $this->billing_cycle === 'yearly';
    }

    /**
     * Verifica se pode fazer downgrade para mensal
     */
    public function canDowngradeToMonthly(): bool
    {
        // Permite downgrade se a assinatura ainda não expirou
        return $this->isActive() || $this->isInGracePeriod();
    }

    /**
     * Calcula a taxa de cancelamento para assinaturas anuais
     */
    public function getCancellationFee(): float
    {
        if (!$this->isYearly() || !$this->plan) {
            return 0.0;
        }

        // Taxa de cancelamento é 30% do valor anual restante
        $monthsRemaining = max(1, $this->getDaysRemaining() / 30);
        $remainingValue = ($this->plan->annual_price / 12) * $monthsRemaining;
        
        return $remainingValue * 0.30;
    }

    /**
     * Calcula quantos dias restam na assinatura
     */
    public function getDaysRemaining(): int
    {
        if (!$this->ends_at) {
            return 0;
        }

        return max(0, now()->diffInDays($this->ends_at, false));
    }

    /**
     * Verifica se precisa de renovação em breve (3 dias)
     */
    public function needsRenewalWarning(): bool
    {
        return $this->getDaysRemaining() <= 3 && $this->isActive();
    }

    /**
     * Ativa a assinatura
     */
    public function activate(): void
    {
        $this->update([
            'status' => 'active',
            'starts_at' => now(),
            'ends_at' => $this->billing_cycle === 'yearly' ? now()->addYear() : now()->addMonth(),
            'grace_period_ends_at' => ($this->billing_cycle === 'yearly' ? now()->addYear() : now()->addMonth())->addDays(3)
        ]);
    }

    /**
     * Expira a assinatura
     */
    public function expire(): void
    {
        $this->update(['status' => 'expired']);
    }

    /**
     * Cancela a assinatura
     */
    public function cancel(): void
    {
        $this->update(['status' => 'cancelled']);
    }
}
