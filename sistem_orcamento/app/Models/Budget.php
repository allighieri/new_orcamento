<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Budget extends Model
{
    protected $fillable = [
        'number',
        'client_id',
        'company_id',
        'total_amount',
        'total_discount',
        'final_amount',
        'issue_date',
        'valid_until',
        'observations',
        'status'
    ];

    protected $casts = [
        'total_amount' => 'decimal:2',
        'total_discount' => 'decimal:2',
        'final_amount' => 'decimal:2',
        'issue_date' => 'date',
        'valid_until' => 'date'
    ];

    /**
     * Relacionamento com cliente
     */
    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    /**
     * Relacionamento com empresa
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * Relacionamento com itens do orçamento
     */
    public function items(): HasMany
    {
        return $this->hasMany(BudgetItem::class);
    }
}
