<?php

namespace App\Http\Controllers;

use App\Models\Plan;
use App\Models\Subscription;
use App\Models\Company;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class SubscriptionController extends Controller
{
    /**
     * Exibir planos disponíveis
     */
    public function index()
    {
        $plans = Plan::where('active', true)->get();
        $company = Auth::user()->company;
        $currentSubscription = $company->activeSubscription();
        
        return view('subscriptions.index', compact('plans', 'company', 'currentSubscription'));
    }
    
    /**
     * Redirecionar para checkout de pagamento
     */
    public function store(Request $request)
    {
        $request->validate([
            'plan_id' => 'required|exists:plans,id',
            'billing_cycle' => 'required|in:monthly,annual'
        ]);
        
        $plan = Plan::findOrFail($request->plan_id);
        
        // Armazenar o ciclo de cobrança na sessão para usar no checkout
        session(['selected_billing_cycle' => $request->billing_cycle]);
        
        // Redirecionar para o checkout de pagamento
        return redirect()->route('payments.checkout', $plan->id)
            ->with('info', 'Complete o pagamento para ativar seu novo plano.');
    }
    
    /**
     * Cancelar assinatura
     */
    public function cancel()
    {
        $company = Auth::user()->company;
        $subscription = $company->activeSubscription();
        
        if (!$subscription) {
            return redirect()->route('subscriptions.index')
                ->with('error', 'Nenhuma assinatura ativa encontrada.');
        }
        
        $subscription->update([
            'status' => 'cancelled',
            'cancelled_at' => now()
        ]);
        
        return redirect()->route('subscriptions.index')
            ->with('success', 'Assinatura cancelada com sucesso!');
    }
    
    /**
     * Reativar assinatura
     */
    public function reactivate()
    {
        $company = Auth::user()->company;
        $subscription = $company->subscriptions()
            ->where('status', 'cancelled')
            ->where('ends_at', '>', now())
            ->first();
        
        if (!$subscription) {
            return redirect()->route('subscriptions.index')
                ->with('error', 'Nenhuma assinatura cancelada encontrada para reativar.');
        }
        
        $subscription->update([
            'status' => 'active',
            'cancelled_at' => null
        ]);
        
        return redirect()->route('subscriptions.index')
            ->with('success', 'Assinatura reativada com sucesso!');
    }
}
