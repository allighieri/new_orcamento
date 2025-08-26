<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class LoginController extends Controller implements HasMiddleware
{
    /**
     * Exibe o formulário de login
     */
    public function showLoginForm()
    {
        return view('auth.login');
    }

    /**
     * Processa o login do usuário
     */
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|string|min:6',
        ], [
            'email.required' => 'O campo email é obrigatório.',
            'email.email' => 'Digite um email válido.',
            'password.required' => 'O campo senha é obrigatório.',
            'password.min' => 'A senha deve ter pelo menos 6 caracteres.',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $credentials = $request->only('email', 'password');
        $remember = $request->has('remember');

        if (Auth::attempt($credentials, $remember)) {
            $request->session()->regenerate();
            
            $user = Auth::user();
            $firstName = explode(' ', $user->name)[0];
            
            // Verificar se o usuário está ativo
            if (!$user->active) {
                Auth::logout();
                return redirect()->route('login')->withErrors([
                    'email' => __('messages.user_inactive')
                ]);
            }
            
            // Redireciona baseado no role do usuário
            if ($user->role === 'super_admin') {
                return redirect()->intended('/dashboard')->with('success', 'Bem-vindo, ' . $user->name . '!');
            } elseif ($user->role === 'admin') {
                return redirect()->intended('/dashboard')->with('success', 'Bem-vindo, ' . $firstName . '!');
            } else {
                return redirect()->intended('/dashboard')->with('success', 'Bem-vindo, ' . $firstName . '!');
            }
        }

        throw ValidationException::withMessages([
            'email' => ['As credenciais fornecidas não conferem com nossos registros.'],
        ]);
    }

    /**
     * Faz logout do usuário
     */
    public function logout(Request $request)
    {
        Auth::logout();
        
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        
        return redirect('/login')->with('success', 'Logout realizado com sucesso!');
    }

    /**
     * Define os middlewares para este controller
     */
    public static function middleware(): array
    {
        return [
            new Middleware('guest', except: ['logout']),
        ];
    }
}