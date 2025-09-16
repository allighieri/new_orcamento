<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Plan extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'description',
        'budget_limit',
        'monthly_price',
        'yearly_price',
        'active'
    ];

    protected $casts = [
        'monthly_price' => 'decimal:2',
        'yearly_price' => 'decimal:2',
        'active' => 'boolean'
    ];

    /**
     * Relacionamento com assinaturas
     */
    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class);
    }

    /**
     * Verifica se o plano é ilimitado
     */
    public function isUnlimited(): bool
    {
        return is_null($this->budget_limit);
    }

    /**
     * Retorna o preço baseado no ciclo de cobrança
     */
    public function getPriceForCycle(string $cycle): float
    {
        return $cycle === 'yearly' ? $this->yearly_price : $this->monthly_price;
    }

    /**
     * Retorna a economia anual em reais
     */
    public function getYearlySavings(): float
    {
        return ($this->monthly_price * 12) - $this->yearly_price;
    }

    /**
     * Retorna a quantidade de orçamentos extras que podem ser comprados
     */
    public function getExtraBudgetsQuantity(): int
    {
        return $this->budget_limit ?? 0;
    }
}
