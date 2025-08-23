<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class Category extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'description',
        'parent_id',
        'level',
        'company_id'
    ];

    /**
     * Relacionamento com categoria pai
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'parent_id');
    }

    /**
     * Relacionamento com subcategorias
     */
    public function children(): HasMany
    {
        return $this->hasMany(Category::class, 'parent_id');
    }

    /**
     * Relacionamento com produtos
     */
    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    /**
     * Relacionamento recursivo com todas as subcategorias (filhos e netos)
     */
    public function allChildren(): HasMany
    {
        return $this->hasMany(Category::class, 'parent_id')
            ->with('allChildren');
    }

    /**
     * Obter todas as categorias em formato de árvore
     */
    public static function getTree($companyId = null)
    {
        $query = static::whereNull('parent_id')
            ->with('allChildren')
            ->orderBy('name');
        
        // Se companyId for null e não houver na sessão, retorna todas (para super_admin)
        if ($companyId !== null) {
            $query->where('company_id', $companyId);
        } elseif (session('tenant_company_id')) {
            $query->where('company_id', session('tenant_company_id'));
        }
        
        return $query->get();
    }

    /**
     * Obter todas as categorias em formato plano para select
     */
    public static function getTreeForSelect($excludeId = null, $companyId = null, $showCompanyName = false)
    {
        $categories = static::getTree($companyId);
        $result = [];
        
        foreach ($categories as $category) {
            static::buildSelectOptions($category, $result, '', $excludeId, $showCompanyName);
        }
        
        return $result;
    }

    /**
     * Construir opções do select recursivamente
     */
    private static function buildSelectOptions($category, &$result, $prefix = '', $excludeId = null, $showCompanyName = false)
    {
        if ($excludeId && $category->id == $excludeId) {
            return;
        }
        
        $categoryName = $category->name;
        
        // Se for categoria pai (sem prefix) e showCompanyName for true, adicionar nome da empresa
        if ($showCompanyName && empty($prefix) && $category->company) {
            $categoryName .= ' (' . $category->company->fantasy_name . ')';
        }
        
        $result[$category->id] = $prefix . $categoryName;
        
        foreach ($category->allChildren as $child) {
            static::buildSelectOptions($child, $result, $prefix . '&nbsp;&nbsp;&nbsp;&nbsp;', $excludeId, $showCompanyName); // 4 &nbsp; por nível
        }
    }

    /**
     * Obter o caminho completo da categoria (breadcrumb)
     */
    public function getFullPath($separator = ' > ')
    {
        $path = [];
        $current = $this;
        
        while ($current) {
            array_unshift($path, $current->name);
            $current = $current->parent;
        }
        
        return implode($separator, $path);
    }

    /**
     * Calcular o nível da categoria automaticamente
     */
    public function calculateLevel()
    {
        $level = 0;
        $current = $this->parent;
        
        while ($current) {
            $level++;
            $current = $current->parent;
        }
        
        return $level;
    }

    /**
     * Relacionamento com empresa
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * Gerar slug automaticamente e calcular nível
     */
    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($category) {
            if (empty($category->slug)) {
                $category->slug = Str::slug($category->name);
            }
            
            // Calcular nível automaticamente
            if ($category->parent_id) {
                $parent = static::find($category->parent_id);
                $category->level = $parent ? $parent->calculateLevel() + 1 : 0;
            } else {
                $category->level = 0;
            }
        });
        
        static::updating(function ($category) {
            // Recalcular nível se o parent_id mudou
            if ($category->isDirty('parent_id')) {
                if ($category->parent_id) {
                    $parent = static::find($category->parent_id);
                    $category->level = $parent ? $parent->calculateLevel() + 1 : 0;
                } else {
                    $category->level = 0;
                }
            }
        });
    }
}
