<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ContactForm extends Model
{
    protected $fillable = [
        'company_id',
        'type',
        'description',
        'active'
    ];

    protected $casts = [
        'active' => 'boolean'
    ];

    const TYPES = [
        'telefone' => 'Telefone',
        'celular' => 'Celular',
        'whatsapp' => 'WhatsApp',
        'email' => 'Email'
    ];

    /**
     * Relacionamento com Company
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * Scope para filtrar apenas registros ativos
     */
    public function scopeActive($query)
    {
        return $query->where('active', true);
    }

    /**
     * Scope para filtrar por empresa
     */
    public function scopeForCompany($query, $companyId)
    {
        return $query->where('company_id', $companyId);
    }
}
