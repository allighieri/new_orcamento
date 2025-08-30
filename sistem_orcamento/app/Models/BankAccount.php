<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class BankAccount extends Model
{
    protected $fillable = [
        'company_id',
        'compe_id',
        'type',
        'branch',
        'account',
        'key',
        'key_desc',
        'description',
        'active'
    ];

    protected $casts = [
        'active' => 'boolean'
    ];

    /**
     * Relacionamento com empresa
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * Relacionamento com banco (compe)
     */
    public function compe(): BelongsTo
    {
        return $this->belongsTo(Compe::class);
    }

    /**
     * Scope para contas ativas
     */
    public function scopeActive($query)
    {
        return $query->where('active', true);
    }

    /**
     * Accessor para exibir informações completas da conta
     */
    public function getFullAccountInfoAttribute()
    {
        $info = $this->description;
        
        if ($this->type === 'Conta' && $this->branch && $this->account) {
            $info .= " - Ag: {$this->branch} Cc: {$this->account}";
        } elseif ($this->type === 'PIX' && $this->key && $this->key_desc) {
            $info .= " - PIX ({$this->key}): {$this->key_desc}";
        }
        
        if ($this->compe) {
            $info .= " ({$this->compe->bank_name})";
        }
        
        return $info;
    }

    /**
     * Accessor para exibir o tipo de chave PIX formatado
     */
    public function getPixKeyTypeAttribute()
    {
        return match($this->key) {
            'CPF' => 'CPF',
            'CNPJ' => 'CNPJ',
            'email' => 'E-mail',
            'telefone' => 'Telefone',
            default => null
        };
    }

    /**
     * Relacionamento com orçamentos através da tabela pivot
     */
    public function budgetBankAccounts(): HasMany
    {
        return $this->hasMany(BudgetBankAccount::class);
    }

    /**
     * Relacionamento many-to-many com orçamentos
     */
    public function budgets(): BelongsToMany
    {
        return $this->belongsToMany(Budget::class, 'budget_bank_accounts')
                    ->withPivot('order')
                    ->withTimestamps()
                    ->orderBy('budget_bank_accounts.order');
    }
}
