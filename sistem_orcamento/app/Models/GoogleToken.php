<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GoogleToken extends Model
{
    protected $fillable = [
        'company_id',
        'access_token',
        'refresh_token',
        'expires_at',
        'token_type',
        'scope'
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'scope' => 'array'
    ];

    /**
     * Relacionamento com a empresa
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * Verifica se o token está expirado
     */
    public function isExpired(): bool
    {
        return $this->expires_at && $this->expires_at->isPast();
    }

    /**
     * Verifica se o token é válido
     */
    public function isValid(): bool
    {
        return !$this->isExpired() && !empty($this->access_token);
    }
}
