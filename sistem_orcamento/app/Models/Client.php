<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Client extends Model
{
    protected $fillable = [
        'fantasy_name',
        'corporate_name',
        'document_number',
        'state_registration',
        'phone',
        'email',
        'address',
        'city',
        'state'
    ];

    /**
     * Relacionamento com contatos
     */
    public function contacts(): HasMany
    {
        return $this->hasMany(Contact::class);
    }

    /**
     * Relacionamento com orçamentos
     */
    public function budgets(): HasMany
    {
        return $this->hasMany(Budget::class);
    }
}
