<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserThemePreference extends Model
{
    protected $fillable = [
        'user_id',
        'company_id',
        'theme'
    ];

    /**
     * Relacionamento com User
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relacionamento com Company
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * Busca ou cria uma preferência de tema para o usuário/empresa
     */
    public static function getThemeForUser($userId, $companyId)
    {
        $preference = self::where('user_id', $userId)
                         ->where('company_id', $companyId)
                         ->first();
        
        return $preference ? $preference->theme : 'blue'; // tema padrão
    }

    /**
     * Define o tema para o usuário/empresa
     */
    public static function setThemeForUser($userId, $companyId, $theme)
    {
        return self::updateOrCreate(
            ['user_id' => $userId, 'company_id' => $companyId],
            ['theme' => $theme]
        );
    }
}
