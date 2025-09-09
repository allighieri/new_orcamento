<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

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
        'delivery_date',
        'delivery_date_enabled',
        'valid_until',
        'observations',
        'status'
    ];

    protected $casts = [
        'total_amount' => 'decimal:2',
        'total_discount' => 'decimal:2',
        'final_amount' => 'decimal:2',
        'issue_date' => 'date',
        'delivery_date' => 'date',
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

    /**
     * Relacionamento com arquivos PDF
     */
    public function pdfFiles(): HasMany
    {
        return $this->hasMany(PdfFile::class);
    }

    /**
     * Relacionamento com pagamentos do orçamento
     */
    public function budgetPayments(): HasMany
    {
        return $this->hasMany(BudgetPayment::class);
    }

    /**
     * Relacionamento com formas de pagamento
     */
    public function payments(): HasMany
    {
        return $this->hasMany(BudgetPayment::class)->ordered();
    }

    /**
     * Relacionamento com contas bancárias do orçamento
     */
    public function budgetBankAccounts(): HasMany
    {
        return $this->hasMany(BudgetBankAccount::class);
    }

    /**
     * Relacionamento many-to-many com contas bancárias
     */
    public function bankAccounts(): BelongsToMany
    {
        return $this->belongsToMany(BankAccount::class, 'budget_bank_accounts')
                    ->withPivot('order')
                    ->withTimestamps()
                    ->orderBy('budget_bank_accounts.order');
    }

    /**
     * Retorna o total de pagamentos configurados
     */
    public function getTotalPaymentsAmountAttribute()
    {
        return $this->payments->sum('amount');
    }

    /**
     * Verifica se o orçamento tem pagamentos configurados
     */
    public function getHasPaymentsAttribute()
    {
        return $this->payments->count() > 0;
    }

    /**
     * Verifica se os pagamentos estão balanceados com o valor final
     */
    public function getPaymentsBalancedAttribute()
    {
        return abs($this->final_amount - $this->total_payments_amount) < 0.01;
    }

    /**
     * Verifica se o orçamento tem contas bancárias configuradas
     */
    public function getHasBankAccountsAttribute()
    {
        return $this->bankAccounts->count() > 0;
    }
}
