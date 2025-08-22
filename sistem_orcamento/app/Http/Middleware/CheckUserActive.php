<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckUserActive
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Verifica se o usuário está autenticado
        if (Auth::check()) {
            $user = Auth::user();
            
            // Se o usuário não está ativo, faz logout e redireciona para login
            if (!$user->active) {
                Auth::logout();
                return redirect()->route('login')
                    ->with('error', __('messages.user_inactive'));
            }
        }
        
        return $next($request);
    }
}
