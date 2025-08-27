<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Compe extends Model
{
    protected $fillable = [
        'bank_name',
        'code',
        'active'
    ];

    protected $casts = [
        'active' => 'boolean'
    ];

    /**
     * Relacionamento com contas bancárias
     */
    public function bankAccounts(): HasMany
    {
        return $this->hasMany(BankAccount::class);
    }

    /**
     * Scope para bancos ativos
     */
    public function scopeActive($query)
    {
        return $query->where('active', true);
    }
}
