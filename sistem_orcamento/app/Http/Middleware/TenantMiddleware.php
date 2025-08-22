<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class TenantMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Verificar se o usuário está autenticado
        if (Auth::check()) {
            $user = Auth::user();
            
            // Se o usuário não é super_admin e não tem empresa associada,
            // redirecionar para uma página de erro ou logout
            if (!$user->isSuperAdmin() && !$user->company_id) {
                Auth::logout();
                return redirect()->route('login')
                    ->withErrors(['error' => 'Usuário não está associado a nenhuma empresa.']);
            }
            
            // Definir o tenant atual na sessão para uso nos controllers
            if ($user->company_id) {
                session(['tenant_company_id' => $user->company_id]);
            } else {
                // Super admin não tem restrição de empresa
                session()->forget('tenant_company_id');
            }
        }
        
        return $next($request);
    }
}
