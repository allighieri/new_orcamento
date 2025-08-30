<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class PaymentMethod extends Model
{
    use SoftDeletes;
    protected $fillable = [
        'company_id',
        'payment_option_method_id',
        'slug',
        'allows_installments',
        'max_installments',
        'is_active'
    ];

    protected $casts = [
        'allows_installments' => 'boolean',
        'is_active' => 'boolean',
        'max_installments' => 'integer'
    ];

    /**
     * Relacionamento com empresa
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * Relacionamento com PaymentOptionMethod
     */
    public function paymentOptionMethod(): BelongsTo
    {
        return $this->belongsTo(PaymentOptionMethod::class);
    }

    /**
     * Relacionamento com pagamentos de orçamentos
     */
    public function budgetPayments(): HasMany
    {
        return $this->hasMany(BudgetPayment::class);
    }

    /**
     * Scope para métodos ativos
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope para métodos que permitem parcelamento
     */
    public function scopeAllowsInstallments($query)
    {
        return $query->where('allows_installments', true);
    }

    /**
     * Scope para métodos de uma empresa específica ou globais
     */
    public function scopeForCompany($query, $companyId = null)
    {
        return $query->where(function($q) use ($companyId) {
            $q->whereNull('company_id') // Métodos globais
              ->orWhere('company_id', $companyId); // Métodos da empresa
        });
    }

    /**
     * Scope para métodos globais (sem empresa)
     */
    public function scopeGlobal($query)
    {
        return $query->whereNull('company_id');
    }

    /**
     * Verifica se é um método global
     */
    public function getIsGlobalAttribute()
    {
        return is_null($this->company_id);
    }
}