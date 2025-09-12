<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\Company;
use App\Models\UsageControl;
use Illuminate\Support\Facades\Auth;

class CheckPlanLimits
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();
        
        // Super admin não tem limitações
        if ($user && $user->role === 'super_admin') {
            return $next($request);
        }

        // Obter empresa do usuário
        $company = $user ? $user->company : null;
        
        if (!$company) {
            return redirect()->route('dashboard')
                           ->with('error', 'Empresa não encontrada.');
        }

        // Verificar se tem assinatura ativa
        $subscription = $company->activeSubscription();
        
        if (!$subscription) {
            return redirect()->route('dashboard')
                           ->with('error', 'Nenhum plano ativo encontrado. Entre em contato com o suporte.');
        }

        // Verificar se a assinatura está expirada
        if ($subscription->isExpired()) {
            return redirect()->route('dashboard')
                           ->with('error', 'Seu plano expirou. Renove sua assinatura para continuar criando orçamentos.');
        }

        // Se está no período de carência, permitir apenas se ainda tem orçamentos
        if ($subscription->isInGracePeriod()) {
            $usageControl = UsageControl::getOrCreateForCurrentMonth(
                $company->id,
                $subscription->id,
                $subscription->plan->budget_limit ?? 0
            );
            
            if (!$usageControl->canCreateBudget()) {
                return redirect()->route('dashboard')
                               ->with('error', 'Seu plano está vencido e você não possui orçamentos disponíveis. Renove sua assinatura.');
            }
        }

        // Verificar limites do plano (apenas se não for ilimitado)
        if (!$subscription->plan->isUnlimited()) {
            $usageControl = UsageControl::getOrCreateForCurrentMonth(
                $company->id,
                $subscription->id,
                $subscription->plan->budget_limit
            );
            
            if (!$usageControl->canCreateBudget()) {
                return redirect()->route('dashboard')
                               ->with('sweetalert', [
                                   'type' => 'warning',
                                   'title' => 'Limite Atingido!',
                                   'text' => 'Você atingiu o limite de orçamentos do seu plano. Adquira orçamentos extras ou faça upgrade do plano.',
                                   'confirmButtonText' => 'OK'
                               ]);
            }
        }

        return $next($request);
    }
}
