<?php

namespace App\Http\Controllers;

use App\Models\PaymentMethod;
use App\Models\Company;
use App\Http\Requests\StorePaymentMethodRequest;
use App\Http\Requests\UpdatePaymentMethodRequest;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Support\Str;

class PaymentMethodController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): View
    {
        $user = auth()->user();
        
        // Obter métodos de pagamento disponíveis para a empresa do usuário
        $paymentMethods = PaymentMethod::forCompany($user->company_id)
            ->with('paymentOptionMethod')
            ->whereNull('deleted_at')
            ->join('payment_option_methods', 'payment_methods.payment_option_method_id', '=', 'payment_option_methods.id')
            ->orderBy('payment_option_methods.method')
            ->select('payment_methods.*')
            ->paginate(10);
            
        return view('payment-methods.index', compact('paymentMethods'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        $paymentOptionMethods = \App\Models\PaymentOptionMethod::orderBy('method')->get();
        
        $companies = collect();
        if (auth()->user()->role === 'super_admin') {
            $companies = \App\Models\Company::orderBy('fantasy_name')->get();
        }
        
        return view('payment-methods.create', compact('paymentOptionMethods', 'companies'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StorePaymentMethodRequest $request): RedirectResponse
    {
        $user = auth()->user();
        
        $validated = $request->validated();

        // Buscar o método de pagamento selecionado para gerar o slug
        $paymentOptionMethod = \App\Models\PaymentOptionMethod::find($validated['payment_option_method_id']);
        $baseSlug = Str::slug($paymentOptionMethod->method);
        $slug = $baseSlug;
        $counter = 1;
        
        // Determinar company_id - se super_admin pode escolher, senão usa a empresa do usuário
        if ($user->role === 'super_admin') {
            $companyId = $validated['company_id'] ?? null;
            if (!$companyId) {
                return redirect()->back()
                    ->withErrors(['company_id' => 'É necessário selecionar uma empresa.'])
                    ->withInput();
            }
        } else {
            $companyId = $user->company_id;
            if (!$companyId) {
                return redirect()->back()
                    ->withErrors(['error' => 'Usuário não possui empresa associada.'])
                    ->withInput();
            }
        }
        
        while (PaymentMethod::where('company_id', $companyId)
                           ->where('slug', $slug)
                           ->withTrashed()
                           ->exists()) {
            $slug = $baseSlug . '-' . $counter;
            $counter++;
        }

        // Definir valores padrão
        $validated['company_id'] = $companyId;
        $validated['slug'] = $slug;
        // Os valores de is_active e allows_installments já foram processados pelo Request
        
        // Se não permite parcelamento, max_installments deve ser 1
        if (!$validated['allows_installments']) {
            $validated['max_installments'] = 1;
        } else {
            $validated['max_installments'] = $validated['max_installments'] ?? 12;
        }

        PaymentMethod::create($validated);

        return redirect()->route('payment-methods.index')
            ->with('success', 'Método de pagamento criado com sucesso!');
    }

    /**
     * Display the specified resource.
     */
    public function show(PaymentMethod $paymentMethod): View
    {
        $user = auth()->user();
        
        // Verificar se o usuário pode visualizar este método
        if (!$this->canUserAccessPaymentMethod($user, $paymentMethod)) {
            abort(403, 'Acesso negado.');
        }
        
        // Carregar estatísticas de uso e relacionamento
        $paymentMethod->loadCount('budgetPayments');
        $paymentMethod->load('paymentOptionMethod');
        
        return view('payment-methods.show', compact('paymentMethod'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(PaymentMethod $paymentMethod): View
    {
        $user = auth()->user();
        
        // Verificar se o usuário pode editar este método
        if (!$this->canUserEditPaymentMethod($user, $paymentMethod)) {
            abort(403, 'Acesso negado.');
        }
        
        $paymentOptionMethods = \App\Models\PaymentOptionMethod::orderBy('method')->get();
        
        $companies = collect();
        if ($user->role === 'super_admin') {
            $companies = \App\Models\Company::orderBy('fantasy_name')->get();
        }
        
        return view('payment-methods.edit', compact('paymentMethod', 'paymentOptionMethods', 'companies'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdatePaymentMethodRequest $request, PaymentMethod $paymentMethod): RedirectResponse
    {
        $user = auth()->user();
        
        // Verificar se o usuário pode editar este método
        if (!$this->canUserEditPaymentMethod($user, $paymentMethod)) {
            abort(403, 'Acesso negado.');
        }
        
        $validated = $request->validated();

        // Determinar company_id - se super_admin pode escolher, senão mantém o atual
        if ($user->role === 'super_admin') {
            $companyId = $validated['company_id'] ?? $paymentMethod->company_id;
        } else {
            $companyId = $paymentMethod->company_id;
        }

        // Gerar novo slug se o método de pagamento mudou ou se a empresa mudou (super_admin)
        $needsNewSlug = ($validated['payment_option_method_id'] !== $paymentMethod->payment_option_method_id) ||
                       ($user->role === 'super_admin' && $companyId !== $paymentMethod->company_id);
                       
        if ($needsNewSlug) {
            $paymentOptionMethod = \App\Models\PaymentOptionMethod::find($validated['payment_option_method_id']);
            $baseSlug = Str::slug($paymentOptionMethod->method);
            $slug = $baseSlug;
            $counter = 1;
            
            while (PaymentMethod::where('company_id', $companyId)
                               ->where('slug', $slug)
                               ->where('id', '!=', $paymentMethod->id)
                               ->withTrashed()
                               ->exists()) {
                $slug = $baseSlug . '-' . $counter;
                $counter++;
            }
            
            $validated['slug'] = $slug;
        }
        
        // Definir company_id no validated
        $validated['company_id'] = $companyId;

        // Os valores de is_active e allows_installments já foram processados pelo Request
        
        // Se não permite parcelamento, max_installments deve ser 1
        if (!$validated['allows_installments']) {
            $validated['max_installments'] = 1;
        } else {
            $validated['max_installments'] = $validated['max_installments'] ?? 12;
        }

        $paymentMethod->update($validated);

        return redirect()->route('payment-methods.index')
            ->with('success', 'Método de pagamento atualizado com sucesso!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(PaymentMethod $paymentMethod): RedirectResponse
    {
        $user = auth()->user();
        
        // Verificar se o usuário pode excluir este método
        if (!$this->canUserEditPaymentMethod($user, $paymentMethod)) {
            abort(403, 'Acesso negado.');
        }
        
        try {
            $paymentMethod->delete();
            
            return redirect()->route('payment-methods.index')
                ->with('success', 'Método de pagamento excluído com sucesso!');
        } catch (\Exception $e) {
            return redirect()->route('payment-methods.index')
                ->with('error', 'Erro ao excluir método de pagamento.');
        }
    }
    
    /**
     * Verificar se o usuário pode acessar o método de pagamento
     */
    private function canUserAccessPaymentMethod($user, PaymentMethod $paymentMethod): bool
    {
        // Super admin pode acessar tudo
        if ($user->role === 'super_admin') {
            return true;
        }
        
        // Métodos globais podem ser acessados por todos
        if ($paymentMethod->is_global) {
            return true;
        }
        
        // Métodos da empresa do usuário
        return $paymentMethod->company_id === $user->company_id;
    }
    
    /**
     * Verificar se o usuário pode editar o método de pagamento
     */
    private function canUserEditPaymentMethod($user, PaymentMethod $paymentMethod): bool
    {
        // Super admin pode editar tudo
        if ($user->role === 'super_admin') {
            return true;
        }
        
        // Métodos globais só podem ser editados por super admin
        if ($paymentMethod->is_global) {
            return false;
        }
        
        // Métodos da empresa do usuário podem ser editados por admin e super_admin
        return in_array($user->role, ['admin', 'super_admin']) && 
               $paymentMethod->company_id === $user->company_id;
    }
}