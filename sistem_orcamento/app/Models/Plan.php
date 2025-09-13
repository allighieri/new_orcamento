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
        'annual_price',
        'features',
        'active'
    ];

    protected $casts = [
        'monthly_price' => 'decimal:2',
        'annual_price' => 'decimal:2',
        'features' => 'array',
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
        return $cycle === 'annual' ? ($this->monthly_price * 12) : $this->monthly_price;
    }

    /**
     * Retorna a economia anual em porcentagem
     */
    public function getAnnualSavingsPercentage(): float
    {
        if ($this->monthly_price == 0) return 0;
        
        $annualPrice = $this->monthly_price * 12;
        return round((($this->monthly_price - $annualPrice) / $this->monthly_price) * 100, 2);
    }
}
