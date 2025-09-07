<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Support\Str;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): View
    {
        $user = auth()->guard('web')->user();
        
        if ($user->role === 'super_admin') {
            // Super admin pode ver todos os produtos
            $query = Product::with(['category', 'company']);
        } else {
            // Admin e user veem apenas produtos da sua empresa
            $companyId = session('tenant_company_id');
            $query = Product::where('company_id', $companyId)
                ->with('category');
        }
        
        // Pesquisar por nome do produto
        if ($request->has('search') && $request->search) {
            $searchTerm = $request->search;
            $query->where('name', 'LIKE', '%' . $searchTerm . '%');
        }
        
        $products = $query->paginate(10)->appends($request->query());
        
        return view('products.index', compact('products'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        $user = auth()->guard('web')->user();
        
        if ($user->role === 'super_admin') {
            // Super admin pode ver todas as categorias e empresas
            $categories = Category::with('company')->get();
            $companies = \App\Models\Company::orderBy('fantasy_name')->get();
        } else {
            // Admin e user veem apenas categorias da sua empresa
            $companyId = session('tenant_company_id');
            $categories = Category::where('company_id', $companyId)->get();
            $companies = collect();
        }
        
        return view('products.create', compact('categories', 'companies'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $user = auth()->guard('web')->user();
        
        $rules = [
            'name' => 'required|string|max:255',
            'price' => 'required|string',
            'description' => 'nullable|string',
            'category_id' => 'required|exists:categories,id',
        ];
        
        // Super admin pode especificar a empresa
        if ($user->role === 'super_admin') {
            $rules['company_id'] = 'required|exists:companies,id';
        }
        
        $validated = $request->validate($rules);
        
        // Converter campos de texto para maiúsculo
        $fieldsToUppercase = ['name', 'description'];
        foreach ($fieldsToUppercase as $field) {
            if (isset($validated[$field]) && !empty($validated[$field])) {
                $validated[$field] = strtoupper($validated[$field]);
            }
        }

        // Converter preço do formato brasileiro para decimal
        $price = str_replace(['.', ','], ['', '.'], $validated['price']);
        $validated['price'] = (float) $price;
        
        // Gerar slug único
        $validated['slug'] = Str::slug($validated['name']);
        
        // Verificar se o slug já existe e torná-lo único
        $originalSlug = $validated['slug'];
        $counter = 1;
        while (Product::where('slug', $validated['slug'])->exists()) {
            $validated['slug'] = $originalSlug . '-' . $counter;
            $counter++;
        }

        // Definir company_id baseado na role do usuário
        if ($user->role === 'super_admin') {
            // Super admin usa o company_id do formulário
            $validated['company_id'] = $validated['company_id'];
        } else {
            // Admin e user usam o tenant da sessão
            $validated['company_id'] = session('tenant_company_id');
        }
        
        $product = Product::create($validated);
        
        // Se for uma requisição AJAX, retornar JSON
        if ($request->ajax()) {
            $product->load('category');
            return response()->json([
                'success' => true,
                'message' => 'Produto cadastrado com sucesso!',
                'product' => [
                    'id' => $product->id,
                    'name' => $product->name,
                    'price' => $product->price,
                    'description' => $product->description,
                    'category_name' => $product->category ? $product->category->name : 'Sem categoria'
                ]
            ]);
        }

        return redirect()->route('products.index')
            ->with('success', 'Produto cadastrado com sucesso!');
    }

    /**
     * Display the specified resource.
     */
    public function show(Product $product): View
    {
        $user = auth()->guard('web')->user();
        
        // Super admin pode ver qualquer produto
        if ($user->role !== 'super_admin') {
            // Verificar se o produto pertence à empresa do usuário
            if ($product->company_id !== session('tenant_company_id')) {
                abort(404);
            }
        }
        
        $product->load('category');
        return view('products.show', compact('product'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Product $product): View
    {
        $user = auth()->guard('web')->user();
        
        // Super admin pode editar qualquer produto
        if ($user->role !== 'super_admin') {
            // Verificar se o produto pertence à empresa do usuário
            if ($product->company_id !== session('tenant_company_id')) {
                abort(404);
            }
        }
        
        if ($user->role === 'super_admin') {
            // Super admin pode ver todas as categorias
            $categories = Category::with('company')->get();
        } else {
            // Admin e user veem apenas categorias da sua empresa
            $companyId = session('tenant_company_id');
            $categories = Category::where('company_id', $companyId)->get();
        }
        return view('products.edit', compact('product', 'categories'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Product $product): RedirectResponse
    {
        $user = auth()->guard('web')->user();
        
        // Super admin pode atualizar qualquer produto
        if ($user->role !== 'super_admin') {
            // Verificar se o produto pertence à empresa do usuário
            if ($product->company_id !== session('tenant_company_id')) {
                abort(404);
            }
        }
        
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'price' => 'required|string',
            'description' => 'nullable|string',
            'category_id' => 'required|exists:categories,id',
        ]);
        
        // Converter campos de texto para maiúsculo
        $fieldsToUppercase = ['name', 'description'];
        foreach ($fieldsToUppercase as $field) {
            if (isset($validated[$field]) && !empty($validated[$field])) {
                $validated[$field] = strtoupper($validated[$field]);
            }
        }

        // Converter preço do formato brasileiro para decimal
        $price = str_replace(['.', ','], ['', '.'], $validated['price']);
        $validated['price'] = (float) $price;
        
        // Gerar slug único se o nome mudou
        if ($product->name !== $validated['name']) {
            $validated['slug'] = Str::slug($validated['name']);
            
            // Verificar se o slug já existe e torná-lo único
            $originalSlug = $validated['slug'];
            $counter = 1;
            while (Product::where('slug', $validated['slug'])->where('id', '!=', $product->id)->exists()) {
                $validated['slug'] = $originalSlug . '-' . $counter;
                $counter++;
            }
        }

        $product->update($validated);

        return redirect()->route('products.index')
            ->with('success', 'Produto atualizado com sucesso!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Product $product): RedirectResponse
    {
        $user = auth()->guard('web')->user();
        
        // Super admin pode deletar qualquer produto
        if ($user->role !== 'super_admin') {
            // Verificar se o produto pertence à empresa do usuário
            if ($product->company_id !== session('tenant_company_id')) {
                abort(404);
            }
        }
        
        try {
            // Primeiro, desassociar o produto dos itens de orçamento (definir product_id como null)
            // para preservar os orçamentos existentes
            \Illuminate\Support\Facades\DB::table('budget_items')
                ->where('product_id', $product->id)
                ->update(['product_id' => null]);
            
            // Depois, excluir o produto
            $product->delete();
            
            return redirect()->route('products.index')
                ->with('success', 'Produto excluído com sucesso! Os orçamentos foram preservados.');
        } catch (\Exception $e) {
            return redirect()->route('products.index')
                ->with('error', 'Erro ao excluir produto: ' . $e->getMessage());
        }
    }

    /**
     * Get products by company for AJAX requests
     */
    public function getProductsByCompany(Request $request)
    {
        $user = auth()->guard('web')->user();
        
        // Apenas super_admin pode acessar este endpoint
        if ($user->role !== 'super_admin') {
            return response()->json(['error' => 'Unauthorized'], 403);
        }
        
        $companyId = $request->get('company_id');
        
        if (!$companyId) {
            return response()->json(['error' => 'Company ID is required'], 400);
        }
        
        $products = Product::where('company_id', $companyId)
            ->with('category')
            ->orderBy('name')
            ->get()
            ->map(function ($product) {
                return [
                    'id' => $product->id,
                    'name' => $product->name,
                    'price' => $product->price,
                    'description' => $product->description,
                    'category_name' => $product->category ? $product->category->name : 'Sem categoria'
                ];
            });
        
        return response()->json($products);
    }
}
