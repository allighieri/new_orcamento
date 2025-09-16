<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class Payment extends Model
{
    protected $fillable = [
        'subscription_id',
        'asaas_payment_id',
        'amount',
        'payment_method',
        'status',
        'pix_qr_code',
        'pix_copy_paste',
        'bank_slip_url',
        'credit_card_info',
        'due_date',
        'confirmed_at',
        'asaas_response'
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'due_date' => 'datetime',
        'confirmed_at' => 'datetime',
        'credit_card_info' => 'array',
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
        $updateData = ['status' => $status];
        
        if ($asaasResponse) {
            $updateData['asaas_response'] = $asaasResponse;
        }
        
        if (in_array($status, ['confirmed', 'received']) && !$this->confirmed_at) {
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
        return $this->payment_method === 'pix';
    }

    /**
     * Verifica se é pagamento por cartão
     */
    public function isCreditCard(): bool
    {
        return $this->payment_method === 'credit_card';
    }

    /**
     * Verifica se é boleto
     */
    public function isBankSlip(): bool
    {
        return $this->payment_method === 'bank_slip';
    }
}
