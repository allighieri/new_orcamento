<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string  ...$roles
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        // Verifica se o usuário está autenticado
        if (!Auth::check()) {
            return redirect('/login')->with('error', 'Você precisa estar logado para acessar esta página.');
        }

        $user = Auth::user();

        // Se não foram especificadas roles, apenas verifica autenticação
        if (empty($roles)) {
            return $next($request);
        }

        // Verifica se o usuário tem uma das roles necessárias
        foreach ($roles as $role) {
            if ($user->role === $role) {
                return $next($request);
            }
        }

        // Se chegou até aqui, o usuário não tem permissão
        abort(403, 'Você não tem permissão para acessar esta página.');
    }
}
