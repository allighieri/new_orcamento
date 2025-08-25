<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BudgetItem extends Model
{
    protected $fillable = [
        'budget_id',
        'product_id',
        'produto',
        'description',
        'quantity',
        'unit_price',
        'discount_percentage',
        'total_price'
    ];

    protected $casts = [
        'quantity' => 'integer',
        'unit_price' => 'decimal:2',
        'discount_percentage' => 'decimal:2',
        'total_price' => 'decimal:2'
    ];

    /**
     * Relacionamento com orçamento
     */
    public function budget(): BelongsTo
    {
        return $this->belongsTo(Budget::class);
    }

    /**
     * Relacionamento com produto
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
