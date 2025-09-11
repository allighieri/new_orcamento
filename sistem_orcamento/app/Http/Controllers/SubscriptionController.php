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
     * Criar nova assinatura
     */
    public function store(Request $request)
    {
        $request->validate([
            'plan_id' => 'required|exists:plans,id',
            'billing_cycle' => 'required|in:monthly,annual'
        ]);
        
        $company = Auth::user()->company;
        $plan = Plan::findOrFail($request->plan_id);
        
        // Cancelar assinatura atual se existir
        $currentSubscription = $company->activeSubscription();
        if ($currentSubscription) {
            $currentSubscription->update([
                'status' => 'cancelled',
                'cancelled_at' => now()
            ]);
        }
        
        // Calcular datas
        $startDate = now();
        $endDate = $request->billing_cycle === 'annual' 
            ? $startDate->copy()->addYear()
            : $startDate->copy()->addMonth();
            
        $price = $request->billing_cycle === 'annual' 
            ? $plan->annual_price 
            : $plan->monthly_price;
        
        // Criar nova assinatura
        Subscription::create([
            'company_id' => $company->id,
            'plan_id' => $plan->id,
            'billing_cycle' => $request->billing_cycle,
            'status' => 'active',
            'start_date' => $startDate,
            'end_date' => $endDate,
            'next_billing_date' => $endDate,
            'amount_paid' => $price
        ]);
        
        return redirect()->route('subscriptions.index')
            ->with('success', 'Plano alterado com sucesso!');
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
