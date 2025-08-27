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
        return view('payment-methods.create', compact('paymentOptionMethods'));
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
        
        while (PaymentMethod::where('company_id', $user->company_id)
                           ->where('slug', $slug)
                           ->exists()) {
            $slug = $baseSlug . '-' . $counter;
            $counter++;
        }

        // Definir valores padrão
        $validated['company_id'] = $user->company_id;
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
        return view('payment-methods.edit', compact('paymentMethod', 'paymentOptionMethods'));
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

        // Gerar novo slug se o método de pagamento mudou
        if ($validated['payment_option_method_id'] !== $paymentMethod->payment_option_method_id) {
            $paymentOptionMethod = \App\Models\PaymentOptionMethod::find($validated['payment_option_method_id']);
            $baseSlug = Str::slug($paymentOptionMethod->method);
            $slug = $baseSlug;
            $counter = 1;
            
            while (PaymentMethod::where('company_id', $paymentMethod->company_id)
                               ->where('slug', $slug)
                               ->where('id', '!=', $paymentMethod->id)
                               ->exists()) {
                $slug = $baseSlug . '-' . $counter;
                $counter++;
            }
            
            $validated['slug'] = $slug;
        }

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
            // Verificar se há pagamentos usando este método
            if ($paymentMethod->budgetPayments()->count() > 0) {
                return redirect()->route('payment-methods.index')
                    ->with('error', 'Não é possível excluir este método de pagamento pois ele está sendo usado em orçamentos.');
            }
            
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