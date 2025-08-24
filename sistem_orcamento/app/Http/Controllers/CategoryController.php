<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class CategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): View
    {
        $user = auth()->guard('web')->user();
        
        if ($user->role === 'super_admin') {
            // Super admin pode ver todas as categorias
            $categories = Category::with(['parent', 'allChildren', 'company'])
                ->withCount('products')
                ->get();
        } else {
            // Admin e user veem apenas categorias da sua empresa
            $companyId = session('tenant_company_id');
            $categories = Category::where('company_id', $companyId)
                ->with(['parent', 'allChildren'])
                ->withCount('products')
                ->get();
        }
        
        // Organizar categorias em formato de árvore para exibição
        $categoriesTree = $this->buildCategoriesTree($categories);
        $isSuperAdmin = $user->role === 'super_admin';

        return view('categories.index', compact('categoriesTree', 'isSuperAdmin'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        $user = auth()->guard('web')->user();
        
        if ($user->role === 'super_admin') {
            // Super admin pode ver todas as categorias e empresas
            $categoriesTree = Category::getTreeForSelect(null, null, true);
            $companies = \App\Models\Company::orderBy('fantasy_name')->get();
        } else {
            // Admin e user veem apenas categorias da sua empresa
            $companyId = session('tenant_company_id');
            $categoriesTree = Category::getTreeForSelect(null, $companyId, false);
            $companies = collect();
        }
        
        return view('categories.create', compact('categoriesTree', 'companies'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $user = auth()->guard('web')->user();
        
        $rules = [
            'name' => 'required|string|max:255|unique:categories',
            'description' => 'nullable|string|max:1000',
            'parent_id' => 'nullable|exists:categories,id',
        ];
        
        // Super admin pode especificar a empresa
        if ($user->role === 'super_admin') {
            $rules['company_id'] = 'required|exists:companies,id';
        }
        
        $validated = $request->validate($rules);

        // Validação adicional para evitar loops
        if ($validated['parent_id']) {
            $this->validateHierarchy(null, $validated['parent_id']);
        }

        // Definir company_id baseado na role do usuário
        if ($user->role === 'super_admin') {
            // Super admin usa o company_id do formulário
            $validated['company_id'] = $validated['company_id'];
        } else {
            // Admin e user usam o tenant da sessão
            $validated['company_id'] = session('tenant_company_id');
        }
        
        $category = Category::create($validated);

        // Se for uma requisição AJAX, retornar JSON
        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Categoria criada com sucesso!',
                'category' => $category
            ]);
        }

        return redirect()->route('categories.index')
            ->with('success', 'Categoria criada com sucesso!');
    }

    /**
     * Display the specified resource.
     */
    public function show(Category $category): View
    {
        $user = auth()->guard('web')->user();
        
        // Super admin pode ver qualquer categoria
        if ($user->role !== 'super_admin') {
            // Verificar se a categoria pertence à empresa do usuário
            if ($category->company_id !== session('tenant_company_id')) {
                abort(404);
            }
        }
        
        $category->load('products');
        return view('categories.show', compact('category'));
    }

    /**
     * Display products of a specific category.
     */
    public function products(Category $category): View
    {
        $user = auth()->guard('web')->user();
        
        // Super admin pode ver produtos de qualquer categoria
        if ($user->role !== 'super_admin') {
            // Verificar se a categoria pertence à empresa do usuário
            if ($category->company_id !== session('tenant_company_id')) {
                abort(404);
            }
        }
        
        $products = $category->products()->paginate(10);
        return view('categories.products', compact('category', 'products'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Category $category): View
    {
        $user = auth()->guard('web')->user();
        
        // Super admin pode editar qualquer categoria
        if ($user->role !== 'super_admin') {
            // Verificar se a categoria pertence à empresa do usuário
            if ($category->company_id !== session('tenant_company_id')) {
                abort(404);
            }
        }
        
        if ($user->role === 'super_admin') {
            // Super admin pode ver todas as categorias
            $categoriesTree = Category::getTreeForSelect($category->id, null, true);
        } else {
            // Admin e user veem apenas categorias da sua empresa
            $companyId = session('tenant_company_id');
            $categoriesTree = Category::getTreeForSelect($category->id, $companyId, false);
        }
        
        return view('categories.edit', compact('category', 'categoriesTree'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Category $category): RedirectResponse
    {
        $user = auth()->guard('web')->user();
        
        // Super admin pode atualizar qualquer categoria
        if ($user->role !== 'super_admin') {
            // Verificar se a categoria pertence à empresa do usuário
            if ($category->company_id !== session('tenant_company_id')) {
                abort(404);
            }
        }
        
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:categories,name,' . $category->id,
            'description' => 'nullable|string|max:1000',
            'parent_id' => 'nullable|exists:categories,id|not_in:' . $category->id,
        ]);

        // Validação adicional para evitar loops na hierarquia
        if ($validated['parent_id']) {
            $this->validateHierarchy($category->id, $validated['parent_id']);
        }

        $category->update($validated);

        return redirect()->route('categories.index')
            ->with('success', 'Categoria atualizada com sucesso!');
    }

    /**
     * Construir árvore de categorias para exibição
     */
    private function buildCategoriesTree($categories)
    {
        $tree = [];
        $categoriesById = $categories->keyBy('id');
        
        // Primeiro, adicionar categorias principais
        foreach ($categories->whereNull('parent_id') as $category) {
            $tree[] = (object) [
                'category' => $category,
                'level' => 0,
                'prefix' => ''
            ];
            
            // Adicionar subcategorias recursivamente
            $this->addChildrenToTree($category, $categoriesById, $tree, 1);
        }
        
        return collect($tree);
    }
    
    /**
     * Adicionar filhos à árvore recursivamente
     */
    private function addChildrenToTree($parent, $categoriesById, &$tree, $level)
    {
        $children = $categoriesById->where('parent_id', $parent->id)->sortBy('name');
        
        foreach ($children as $child) {
            $prefix = str_repeat('    ', $level); // 4 espaços por nível
            
            $tree[] = (object) [
                'category' => $child,
                'level' => $level,
                'prefix' => $prefix
            ];
            
            // Adicionar filhos do filho recursivamente
            $this->addChildrenToTree($child, $categoriesById, $tree, $level + 1);
        }
    }

    /**
     * Validar hierarquia para evitar loops
     */
    private function validateHierarchy($categoryId, $parentId)
    {
        if (!$parentId) {
            return;
        }

        // Verificar se o parent_id não é um descendente da categoria atual
        $parent = Category::find($parentId);
        $current = $parent;
        
        while ($current) {
            if ($current->id == $categoryId) {
                throw new \InvalidArgumentException('Não é possível definir uma subcategoria como categoria pai. Isso criaria um loop na hierarquia.');
            }
            $current = $current->parent;
        }
    }

    /**
     * Coletar todas as subcategorias recursivamente
     */
    private function getAllSubcategories($categoryId, &$allIds = [])
    {
        $allIds[] = $categoryId;
        
        $subcategories = Category::where('parent_id', $categoryId)->pluck('id');
        
        foreach ($subcategories as $subcategoryId) {
            $this->getAllSubcategories($subcategoryId, $allIds);
        }
        
        return $allIds;
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Category $category): RedirectResponse
    {
        $user = auth()->guard('web')->user();
        
        // Super admin pode deletar qualquer categoria
        if ($user->role !== 'super_admin') {
            // Verificar se a categoria pertence à empresa do usuário
            if ($category->company_id !== session('tenant_company_id')) {
                abort(404);
            }
        }
        
        try {
            // Coletar todas as categorias que serão excluídas (categoria principal + todas as subcategorias)
            $allCategoryIds = $this->getAllSubcategories($category->id);
            
            // Contar produtos que serão excluídos de todas as categorias
            $productsCount = \Illuminate\Support\Facades\DB::table('products')->whereIn('category_id', $allCategoryIds)->count();
            
            // Primeiro, desassociar os produtos dos itens de orçamento (definir product_id como null)
            // para preservar os orçamentos existentes
            $productIds = \Illuminate\Support\Facades\DB::table('products')->whereIn('category_id', $allCategoryIds)->pluck('id');
            if ($productIds->isNotEmpty()) {
                \Illuminate\Support\Facades\DB::table('budget_items')->whereIn('product_id', $productIds)->update(['product_id' => null]);
            }
            
            // Depois, excluir todos os produtos das categorias
            \Illuminate\Support\Facades\DB::table('products')->whereIn('category_id', $allCategoryIds)->delete();
            
            // Por fim, excluir todas as categorias (das folhas para a raiz)
            // Ordenar por nível decrescente para excluir primeiro as subcategorias
            $categoriesToDelete = Category::whereIn('id', $allCategoryIds)->get()->sortByDesc(function($cat) {
                return $this->getCategoryLevel($cat);
            });
            
            foreach ($categoriesToDelete as $cat) {
                $cat->delete();
            }
            
            $categoriesCount = count($allCategoryIds);
            $message = 'Categoria excluída com sucesso!';
            
            if ($categoriesCount > 1) {
                $subcategoriesCount = $categoriesCount - 1;
                $message .= " {$subcategoriesCount} subcategoria(s) também foram excluídas.";
            }
            
            if ($productsCount > 0) {
                $message .= " {$productsCount} produto(s) também foram excluídos. Os orçamentos foram preservados.";
            }
            
            return redirect()->route('categories.index')
                ->with('success', $message);
        } catch (\Exception $e) {
            return redirect()->route('categories.index')
                ->with('error', 'Erro ao excluir categoria: ' . $e->getMessage());
        }
    }
    
    /**
     * Calcular o nível de uma categoria na hierarquia
     */
    private function getCategoryLevel($category, $level = 0)
    {
        if (!$category->parent_id) {
            return $level;
        }
        
        $parent = Category::find($category->parent_id);
        if (!$parent) {
            return $level;
        }
        
        return $this->getCategoryLevel($parent, $level + 1);
    }

    /**
     * Get categories by company for AJAX requests
     */
    public function getCategoriesByCompany(Request $request)
    {
        $user = auth()->guard('web')->user();
        
        // Apenas super_admin pode usar este endpoint
        if ($user->role !== 'super_admin') {
            return response()->json(['error' => 'Unauthorized'], 403);
        }
        
        $companyId = $request->get('company_id');
        
        if (!$companyId) {
            return response()->json([
                'success' => true,
                'categories' => []
            ]);
        }
        
        $categoriesTree = Category::getTreeForSelect(null, $companyId, false);
        
        return response()->json([
            'success' => true,
            'categories' => $categoriesTree
        ]);
    }
}
