<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class RegisterController extends Controller implements HasMiddleware
{
    /**
     * Exibe o formulário de registro
     */
    public function showRegistrationForm()
    {
        return view('auth.register');
    }

    /**
     * Processa o registro de um novo usuário
     */
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6|confirmed',
            'role' => 'sometimes|in:user,admin,super_admin',
        ], [
            'name.required' => 'O campo nome é obrigatório.',
            'name.max' => 'O nome não pode ter mais de 255 caracteres.',
            'email.required' => 'O campo email é obrigatório.',
            'email.email' => 'Digite um email válido.',
            'email.unique' => 'Este email já está em uso.',
            'password.required' => 'O campo senha é obrigatório.',
            'password.min' => 'A senha deve ter pelo menos 6 caracteres.',
            'password.confirmed' => 'A confirmação da senha não confere.',
            'role.in' => 'Role inválida.',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        // Apenas super_admin pode criar usuários com role admin ou super_admin
        $role = 'user'; // Padrão
        if ($request->has('role') && Auth::check() && Auth::user()->role === 'super_admin') {
            $role = $request->role;
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $role,
            'active' => 0, // Novos usuários são criados como inativos
        ]);

        // Se for um registro público (sem estar logado), redireciona para login com mensagem
        if (!Auth::check()) {
            return redirect()->route('login')->with('success', 'Conta criada com sucesso! Aguarde até que um admin lhe dê permissão para logar no sistema.');
        }

        // Se for um admin criando usuário, redireciona para lista de usuários
        return redirect()->back()->with('success', 'Usuário criado com sucesso!');
    }

    /**
     * Define os middlewares para este controller
     */
    public static function middleware(): array
    {
        return [
            new Middleware('guest', only: ['showRegistrationForm']),
        ];
    }
}