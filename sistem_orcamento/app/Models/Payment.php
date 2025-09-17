<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class Payment extends Model
{
    protected $fillable = [
        'company_id',
        'subscription_id',
        'plan_id',
        'asaas_payment_id',
        'asaas_customer_id',
        'asaas_subscription_id',
        'payment_id',
        'amount',
        'billing_type',
        'billing_cycle',
        'type',
        'status',
        'due_date',
        'confirmed_at',
        'description',
        'extra_budgets_quantity',
        'pix_qr_code',
        'pix_copy_paste',
        'bank_slip_url',
        'credit_card_info',
        'asaas_response'
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'due_date' => 'datetime',
        'confirmed_at' => 'datetime',
        'asaas_response' => 'array'
    ];

    /**
     * Relacionamento com Subscription
     */
    public function subscription(): BelongsTo
    {
        return $this->belongsTo(Subscription::class);
    }

    /**
     * Relacionamento com Plan
     */
    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class);
    }

    /**
     * Relacionamento com Company
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * Verifica se o pagamento foi aprovado
     */
    public function isPaid(): bool
    {
        return in_array($this->status, ['confirmed', 'received']);
    }

    /**
     * Verifica se o pagamento está pendente
     */
    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    /**
     * Verifica se o pagamento está vencido
     */
    public function isOverdue(): bool
    {
        return $this->status === 'overdue' || ($this->status === 'pending' && $this->due_date < now());
    }

    /**
     * Marca o pagamento como pago
     */
    public function markAsPaid(): void
    {
        $this->update([
            'status' => 'confirmed',
            'confirmed_at' => now()
        ]);
    }

    /**
     * Atualiza o status do pagamento
     */
    public function updateStatus(string $status, array $asaasResponse = null): void
    {
        // Converter status para minúsculo para padronização
        $normalizedStatus = strtolower($status);
        $updateData = ['status' => $normalizedStatus];
        
        if ($asaasResponse) {
            $updateData['asaas_response'] = $asaasResponse;
        }
        
        if (in_array($normalizedStatus, ['confirmed', 'received']) && !$this->confirmed_at) {
            $updateData['confirmed_at'] = now();
        }
        
        $this->update($updateData);
    }

    /**
     * Scope para pagamentos pendentes
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope para pagamentos aprovados
     */
    public function scopePaid($query)
    {
        return $query->whereIn('status', ['confirmed', 'received']);
    }

    /**
     * Scope para pagamentos vencidos
     */
    public function scopeOverdue($query)
    {
        return $query->where(function($q) {
            $q->where('status', 'overdue')
              ->orWhere(function($subQ) {
                  $subQ->where('status', 'pending')
                       ->where('due_date', '<', now());
              });
        });
    }

    /**
     * Verifica se é pagamento PIX
     */
    public function isPix(): bool
    {
        return $this->billing_type === 'PIX';
    }

    /**
     * Verifica se é pagamento por cartão
     */
    public function isCreditCard(): bool
    {
        return $this->billing_type === 'CREDIT_CARD';
    }

    /**
     * Verifica se é boleto
     */
    public function isBankSlip(): bool
    {
        return $this->billing_type === 'BOLETO';
    }
}
