<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class Payment extends Model
{
    protected $fillable = [
        'company_id',
        'plan_id',
        'asaas_payment_id',
        'asaas_customer_id',
        'amount',
        'billing_type',
        'status',
        'due_date',
        'description',
        'payment_data',
        'webhook_data',
        'paid_at',
        'extra_budgets_quantity'
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'due_date' => 'date',
        'paid_at' => 'datetime',
        'payment_data' => 'array',
        'webhook_data' => 'array'
    ];

    /**
     * Relacionamento com Company
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * Relacionamento com Plan
     */
    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class);
    }

    /**
     * Verifica se o pagamento foi aprovado
     */
    public function isPaid(): bool
    {
        return in_array($this->status, ['RECEIVED', 'CONFIRMED', 'RECEIVED_IN_CASH']);
    }

    /**
     * Verifica se o pagamento está pendente
     */
    public function isPending(): bool
    {
        return $this->status === 'PENDING';
    }

    /**
     * Verifica se o pagamento está vencido
     */
    public function isOverdue(): bool
    {
        return $this->status === 'OVERDUE' || ($this->status === 'PENDING' && $this->due_date < now());
    }

    /**
     * Marca o pagamento como pago
     */
    public function markAsPaid(): void
    {
        $this->update([
            'status' => 'RECEIVED',
            'paid_at' => now()
        ]);
    }

    /**
     * Atualiza o status do pagamento
     */
    public function updateStatus(string $status, array $webhookData = null): void
    {
        $updateData = ['status' => $status];
        
        if ($webhookData) {
            $updateData['webhook_data'] = $webhookData;
        }
        
        if (in_array($status, ['RECEIVED', 'CONFIRMED', 'RECEIVED_IN_CASH']) && !$this->paid_at) {
            $updateData['paid_at'] = now();
        }
        
        $this->update($updateData);
    }

    /**
     * Scope para pagamentos pendentes
     */
    public function scopePending($query)
    {
        return $query->where('status', 'PENDING');
    }

    /**
     * Scope para pagamentos aprovados
     */
    public function scopePaid($query)
    {
        return $query->whereIn('status', ['RECEIVED', 'CONFIRMED', 'RECEIVED_IN_CASH']);
    }

    /**
     * Scope para pagamentos vencidos
     */
    public function scopeOverdue($query)
    {
        return $query->where(function($q) {
            $q->where('status', 'OVERDUE')
              ->orWhere(function($subQ) {
                  $subQ->where('status', 'PENDING')
                       ->where('due_date', '<', now());
              });
        });
    }
}
