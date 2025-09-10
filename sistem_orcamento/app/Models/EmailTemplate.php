<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class EmailTemplate extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_id',
        'name',
        'subject',
        'html_content',
        'variables',
        'description',
        'is_active',
        'header_text',
        'header2_text',
        'initial_message',
        'final_message',
        'footer_text',
        'show_budget_number',
        'show_budget_value',
        'show_budget_date',
        'show_budget_validity',
        'show_delivery_date'
    ];

    protected $casts = [
        'variables' => 'array',
        'is_active' => 'boolean',
        'show_budget_number' => 'boolean',
        'show_budget_value' => 'boolean',
        'show_budget_date' => 'boolean',
        'show_budget_validity' => 'boolean',
        'show_delivery_date' => 'boolean'
    ];

    /**
     * Relacionamento com Company
     */
    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * Scope para templates ativos
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope para templates de uma empresa específica
     */
    public function scopeForCompany($query, $companyId)
    {
        return $query->where('company_id', $companyId);
    }

    /**
     * Renderiza o template com as variáveis fornecidas
     */
    public function render($variables = [], $content = null)
    {
        $content = $content ?? $this->html_content;
        
        foreach ($variables as $key => $value) {
            $content = str_replace('{{' . $key . '}}', $value, $content);
        }
        
        return $content;
    }

    /**
     * Obtém as variáveis disponíveis no template
     */
    public function getAvailableVariables()
    {
        return $this->variables ?? [];
    }
}
