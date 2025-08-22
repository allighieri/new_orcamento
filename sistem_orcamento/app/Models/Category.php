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
        'level'
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
        return $this->hasMany(Category::class, 'parent_id')->with('allChildren');
    }

    /**
     * Obter todas as categorias em formato de árvore
     */
    public static function getTree()
    {
        return static::whereNull('parent_id')
            ->with('allChildren')
            ->orderBy('name')
            ->get();
    }

    /**
     * Obter todas as categorias em formato plano para select
     */
    public static function getTreeForSelect($excludeId = null)
    {
        $categories = static::getTree();
        $result = [];
        
        foreach ($categories as $category) {
            static::buildSelectOptions($category, $result, '', $excludeId);
        }
        
        return $result;
    }

    /**
     * Construir opções do select recursivamente
     */
    private static function buildSelectOptions($category, &$result, $prefix = '', $excludeId = null)
    {
        if ($excludeId && $category->id == $excludeId) {
            return;
        }
        
        $result[$category->id] = $prefix . $category->name;
        
        foreach ($category->allChildren as $child) {
            static::buildSelectOptions($child, $result, $prefix . '&nbsp;&nbsp;&nbsp;&nbsp;', $excludeId); // 4 &nbsp; por nível
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
