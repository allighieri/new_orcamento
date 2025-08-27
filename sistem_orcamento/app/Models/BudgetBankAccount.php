<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BudgetBankAccount extends Model
{
    protected $fillable = [
        'budget_id',
        'bank_account_id',
        'order'
    ];

    protected $casts = [
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
     * Relacionamento com conta bancária
     */
    public function bankAccount(): BelongsTo
    {
        return $this->belongsTo(BankAccount::class);
    }

    /**
     * Scope para ordenar por ordem
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('order');
    }
}