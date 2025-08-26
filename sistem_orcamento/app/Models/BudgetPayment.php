<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Carbon\Carbon;

class BudgetPayment extends Model
{
    protected $fillable = [
        'budget_id',
        'payment_method_id',
        'amount',
        'installments',
        'payment_moment',
        'custom_date',
        'days_after_pickup',
        'notes',
        'order'
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'installments' => 'integer',
        'custom_date' => 'date',
        'days_after_pickup' => 'integer',
        'order' => 'integer'
    ];

    /**
     * Relacionamento com orçamento
     */
    public function budget(): BelongsTo
    {
        return $this->belongsTo(Budget::class);
    }

    /**
     * Relacionamento com método de pagamento
     */
    public function paymentMethod(): BelongsTo
    {
        return $this->belongsTo(PaymentMethod::class);
    }

    /**
     * Relacionamento com parcelas
     */
    public function installments(): HasMany
    {
        return $this->hasMany(PaymentInstallment::class)->orderBy('installment_number');
    }

    /**
     * Calcula o valor de cada parcela
     */
    public function getInstallmentAmountAttribute()
    {
        if ($this->installments <= 1) {
            return $this->amount;
        }
        
        return round($this->amount / $this->installments, 2);
    }

    /**
     * Retorna a descrição do momento de pagamento
     */
    public function getPaymentMomentDescriptionAttribute()
    {
        return match($this->payment_moment) {
            'approval' => 'Na aprovação do orçamento',
            'pickup' => 'Na retirada do produto',
            'custom' => 'Data customizada: ' . $this->custom_date?->format('d/m/Y'),
            default => 'Não definido'
        };
    }

    /**
     * Calcula a data de vencimento baseada no momento de pagamento
     */
    public function calculateDueDate($pickupDate = null)
    {
        return match($this->payment_moment) {
            'approval' => now()->toDateString(),
            'pickup' => $pickupDate ?? now()->toDateString(),
            'custom' => $this->custom_date?->toDateString(),
            default => now()->toDateString()
        };
    }

    /**
     * Scope para ordenar por ordem de exibição
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('order');
    }
}