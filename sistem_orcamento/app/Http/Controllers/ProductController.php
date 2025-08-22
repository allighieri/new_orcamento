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
    public function index(): View
    {
        $companyId = session('tenant_company_id');
        $products = Product::where('company_id', $companyId)
            ->with('category')
            ->paginate(10);
        return view('products.index', compact('products'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        $companyId = session('tenant_company_id');
        $categories = Category::where('company_id', $companyId)->get();
        return view('products.create', compact('categories'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'price' => 'required|string',
            'description' => 'nullable|string',
            'category_id' => 'required|exists:categories,id',
        ]);

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

        $validated['company_id'] = session('tenant_company_id');
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
        // Verificar se o produto pertence à empresa do usuário
        if ($product->company_id !== session('tenant_company_id')) {
            abort(404);
        }
        
        $product->load('category');
        return view('products.show', compact('product'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Product $product): View
    {
        // Verificar se o produto pertence à empresa do usuário
        if ($product->company_id !== session('tenant_company_id')) {
            abort(404);
        }
        
        $companyId = session('tenant_company_id');
        $categories = Category::where('company_id', $companyId)->get();
        return view('products.edit', compact('product', 'categories'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Product $product): RedirectResponse
    {
        // Verificar se o produto pertence à empresa do usuário
        if ($product->company_id !== session('tenant_company_id')) {
            abort(404);
        }
        
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'price' => 'required|string',
            'description' => 'nullable|string',
            'category_id' => 'required|exists:categories,id',
        ]);

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
        // Verificar se o produto pertence à empresa do usuário
        if ($product->company_id !== session('tenant_company_id')) {
            abort(404);
        }
        
        try {
            $product->delete();
            return redirect()->route('products.index')
                ->with('success', 'Produto excluído com sucesso!');
        } catch (\Exception $e) {
            return redirect()->route('products.index')
                ->with('error', 'Erro ao excluir produto. Verifique se não há registros relacionados.');
        }
    }
}
