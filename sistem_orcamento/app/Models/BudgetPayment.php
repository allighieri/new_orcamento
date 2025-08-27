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
    public function paymentInstallments(): HasMany
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
        $deliveryDate = $this->budget->delivery_date ?? now();
        
        return match($this->payment_moment) {
            'approval' => $deliveryDate->toDateString(),
            'pickup' => $deliveryDate->toDateString(),
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

    /**
     * Cria as parcelas automaticamente após salvar o pagamento
     */
    public function createInstallments($pickupDate = null)
    {
        // Remove parcelas existentes
        $this->paymentInstallments()->delete();

        // Se for apenas 1 parcela, cria uma única parcela
        if ($this->installments <= 1) {
            PaymentInstallment::create([
                'budget_payment_id' => $this->id,
                'installment_number' => 1,
                'amount' => $this->amount,
                'due_date' => $this->calculateDueDate($pickupDate),
                'status' => 'pending'
            ]);
            return;
        }

        // Calcula o valor de cada parcela
        $installmentAmount = round($this->amount / $this->installments, 2);
        $totalDistributed = 0;

        // Cria as parcelas
        for ($i = 1; $i <= $this->installments; $i++) {
            // Para a última parcela, ajusta o valor para garantir que a soma seja exata
            if ($i == $this->installments) {
                $currentAmount = $this->amount - $totalDistributed;
            } else {
                $currentAmount = $installmentAmount;
                $totalDistributed += $currentAmount;
            }

            // Calcula a data de vencimento baseada no momento de pagamento
            $dueDate = $this->calculateInstallmentDueDate($i, $pickupDate);

            PaymentInstallment::create([
                'budget_payment_id' => $this->id,
                'installment_number' => $i,
                'amount' => $currentAmount,
                'due_date' => $dueDate,
                'status' => 'pending'
            ]);
        }
    }

    /**
     * Calcula a data de vencimento de uma parcela específica
     */
    private function calculateInstallmentDueDate($installmentNumber, $pickupDate = null)
    {
        $deliveryDate = $this->budget->delivery_date ?? now();
        
        $baseDate = match($this->payment_moment) {
            'approval' => Carbon::parse($deliveryDate),
            'pickup' => Carbon::parse($deliveryDate),
            'custom' => $this->custom_date ? Carbon::parse($this->custom_date) : now(),
            default => now()
        };

        // Para parcelas múltiplas, adiciona 30 dias para cada parcela subsequente
        if ($installmentNumber > 1) {
            $baseDate->addMonths($installmentNumber - 1);
        }

        return $baseDate->toDateString();
    }
}