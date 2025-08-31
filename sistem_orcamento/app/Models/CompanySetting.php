<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CompanySetting extends Model
{
    protected $fillable = [
        'company_id',
        'budget_validity_days',
        'budget_delivery_days',
        'enable_pdf_watermark',
        'show_validity_as_text',
        'border',
    ];

    protected $casts = [
        'enable_pdf_watermark' => 'boolean',
        'show_validity_as_text' => 'boolean',
    ];

    /**
     * Relacionamento com Company
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * Obter configurações para uma empresa específica
     */
    public static function getForCompany(int $companyId): self
    {
        return self::firstOrCreate(
            ['company_id' => $companyId],
            [
                'budget_validity_days' => 30,
                'budget_delivery_days' => 30,
                'enable_pdf_watermark' => true,
                'show_validity_as_text' => false,
                'border' => 0,
            ]
        );
    }
}
