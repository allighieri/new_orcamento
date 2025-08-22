<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\Company;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RequireCompanyMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Verificar se o usuário está autenticado
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $user = Auth::user();

        // Verifica se há pelo menos uma empresa cadastrada
        if (Company::count() === 0) {
            if ($user->isSuperAdmin()) {
                // Super admin recebe aviso na dashboard
                return redirect()->route('dashboard')
                    ->with('warning', __('messages.company_required_admin'));
            } else {
                // Usuários normais não podem acessar sem empresa
                return redirect()->route('dashboard')
                    ->with('error', __('messages.company_required'));
            }
        }

        // Super admin pode acessar mesmo com empresa cadastrada
        if ($user->isSuperAdmin()) {
            return $next($request);
        }

        // Verificar se o usuário tem empresa vinculada
        if (!$user->company_id) {
            return redirect()->route('dashboard')
                ->with('error', __('messages.user_no_company'));
        }

        return $next($request);
    }
}