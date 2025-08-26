<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class PaymentInstallment extends Model
{
    protected $fillable = [
        'budget_payment_id',
        'installment_number',
        'amount',
        'due_date',
        'status',
        'paid_date',
        'paid_amount',
        'notes'
    ];

    protected $casts = [
        'installment_number' => 'integer',
        'amount' => 'decimal:2',
        'due_date' => 'date',
        'paid_date' => 'date',
        'paid_amount' => 'decimal:2'
    ];

    /**
     * Relacionamento com pagamento do orçamento
     */
    public function budgetPayment(): BelongsTo
    {
        return $this->belongsTo(BudgetPayment::class);
    }

    /**
     * Retorna a descrição do status
     */
    public function getStatusDescriptionAttribute()
    {
        return match($this->status) {
            'pending' => 'Pendente',
            'paid' => 'Paga',
            'overdue' => 'Vencida',
            'cancelled' => 'Cancelada',
            default => 'Indefinido'
        };
    }

    /**
     * Retorna a classe CSS para o status
     */
    public function getStatusClassAttribute()
    {
        return match($this->status) {
            'pending' => 'text-warning',
            'paid' => 'text-success',
            'overdue' => 'text-danger',
            'cancelled' => 'text-muted',
            default => 'text-secondary'
        };
    }

    /**
     * Verifica se a parcela está vencida
     */
    public function getIsOverdueAttribute()
    {
        if ($this->status === 'paid' || $this->status === 'cancelled') {
            return false;
        }
        
        return $this->due_date && $this->due_date->isPast();
    }

    /**
     * Retorna o valor pendente (diferença entre valor da parcela e valor pago)
     */
    public function getPendingAmountAttribute()
    {
        if ($this->status === 'paid') {
            return 0;
        }
        
        return $this->amount - ($this->paid_amount ?? 0);
    }

    /**
     * Scope para parcelas pendentes
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope para parcelas pagas
     */
    public function scopePaid($query)
    {
        return $query->where('status', 'paid');
    }

    /**
     * Scope para parcelas vencidas
     */
    public function scopeOverdue($query)
    {
        return $query->where('status', 'pending')
                    ->where('due_date', '<', now());
    }

    /**
     * Scope para ordenar por número da parcela
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('installment_number');
    }
}