<?php

namespace App\Http\Controllers;

use App\Models\PaymentOptionMethod;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class PaymentOptionMethodController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): View
    {
        $query = PaymentOptionMethod::query();
        
        // Filtro de busca
        if ($request->filled('search')) {
            $search = $request->get('search');
            $query->where(function($q) use ($search) {
                $q->where('method', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }
        
        // Filtro de status
        if ($request->filled('status')) {
            $status = $request->get('status');
            if ($status === 'active') {
                $query->where('active', true);
            } elseif ($status === 'inactive') {
                $query->where('active', false);
            }
        }
        
        $paymentOptionMethods = $query->withCount('paymentMethods')->orderBy('method')->paginate(15);
        
        return view('payment-option-methods.index', compact('paymentOptionMethods'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        return view('payment-option-methods.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'method' => 'required|string|max:255|unique:payment_option_methods,method',
            'description' => 'nullable|string|max:1000',
            'active' => 'boolean'
        ]);
        
        $validated['active'] = $request->has('active');
        
        PaymentOptionMethod::create($validated);
        
        return redirect()->route('payment-option-methods.index')
                        ->with('success', 'Método de opção de pagamento criado com sucesso!');
    }

    /**
     * Display the specified resource.
     */
    public function show(PaymentOptionMethod $paymentOptionMethod): View
    {
        $paymentOptionMethod->load('paymentMethods');
        
        return view('payment-option-methods.show', compact('paymentOptionMethod'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(PaymentOptionMethod $paymentOptionMethod): View
    {
        return view('payment-option-methods.edit', compact('paymentOptionMethod'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, PaymentOptionMethod $paymentOptionMethod): RedirectResponse
    {
        $validated = $request->validate([
            'method' => 'required|string|max:255|unique:payment_option_methods,method,' . $paymentOptionMethod->id,
            'description' => 'nullable|string|max:1000',
            'active' => 'required|boolean'
        ]);
        
        $paymentOptionMethod->update($validated);
        
        return redirect()->route('payment-option-methods.index')
                        ->with('success', 'Método de opção de pagamento atualizado com sucesso!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(PaymentOptionMethod $paymentOptionMethod): RedirectResponse
    {
        try {
            // Verificar se há métodos de pagamento associados
            if ($paymentOptionMethod->paymentMethods()->count() > 0) {
                return redirect()->route('payment-option-methods.index')
                                ->with('error', 'Não é possível excluir este método pois existem métodos de pagamento associados a ele.');
            }
            
            $paymentOptionMethod->delete();
            
            return redirect()->route('payment-option-methods.index')
                            ->with('success', 'Método de opção de pagamento excluído com sucesso!');
        } catch (\Exception $e) {
            return redirect()->route('payment-option-methods.index')
                            ->with('error', 'Erro ao excluir método: ' . $e->getMessage());
        }
    }

    /**
     * Toggle payment option method active status.
     */
    public function toggleActive(PaymentOptionMethod $paymentOptionMethod): RedirectResponse
    {
        $paymentOptionMethod->update(['active' => !$paymentOptionMethod->active]);
        
        $status = $paymentOptionMethod->active ? 'ativado' : 'desativado';
        
        return redirect()->route('payment-option-methods.index')
            ->with('success', "Método de pagamento {$status} com sucesso!");
    }
}
