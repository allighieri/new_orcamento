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
        'starts_at',
        'ends_at',
        'grace_period_ends_at',
        'remaining_budgets',
        'auto_renew'
    ];

    protected $casts = [
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
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
