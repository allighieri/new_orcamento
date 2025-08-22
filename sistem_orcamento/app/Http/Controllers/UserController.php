<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Company;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): View
    {
        $user = Auth::user();
        
        if ($user->role === 'super_admin') {
            // Super admin vê todos os usuários
            $users = User::with('company')->paginate(10);
        } else {
            // Admin vê apenas usuários da sua empresa
            $users = User::with('company')
                ->where('company_id', $user->company_id)
                ->paginate(10);
        }
        
        return view('users.index', compact('users'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        $user = Auth::user();
        
        if ($user->role === 'super_admin') {
            // Super admin pode associar usuário a qualquer empresa
            $companies = Company::all();
        } else {
            // Admin só pode associar usuário à sua própria empresa
            $companies = Company::where('id', $user->company_id)->get();
        }
        
        return view('users.create', compact('companies'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $user = Auth::user();
        
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
            'role' => ['required', Rule::in(['user', 'admin', 'super_admin'])],
            'company_id' => [
                Rule::requiredIf(function () use ($request) {
                    return in_array($request->role, ['user', 'admin']);
                }),
                'nullable',
                'exists:companies,id'
            ],
            'active' => 'boolean'
        ], [
            'password.confirmed' => 'A confirmação da senha não confere.',
            'company_id.required' => 'Campo obrigatório para user ou admin.'
        ]);

        // Verificar permissões de role
        if ($user->role !== 'super_admin' && $validated['role'] === 'super_admin') {
            return back()->withErrors([
                'role' => 'Você não tem permissão para criar super administradores.'
            ])->withInput();
        }

        // Verificar permissões de empresa
        if ($user->role !== 'super_admin') {
            // Admin só pode associar usuário à sua própria empresa
            $validated['company_id'] = $user->company_id;
        }

        // Hash da senha
        $validated['password'] = Hash::make($validated['password']);
        
        // Definir status ativo (padrão: inativo)
        $validated['active'] = $validated['active'] ?? false;

        User::create($validated);

        return redirect()->route('users.index')
            ->with('success', 'Usuário criado com sucesso!');
    }

    /**
     * Display the specified resource.
     */
    public function show(User $user): View
    {
        $currentUser = Auth::user();
        
        // Verificar se o usuário atual pode visualizar este usuário
        if ($currentUser->role !== 'super_admin' && $user->company_id !== $currentUser->company_id) {
            abort(403, 'Você não tem permissão para visualizar este usuário.');
        }
        
        $user->load('company');
        return view('users.show', compact('user'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(User $user): View
    {
        $currentUser = Auth::user();
        
        // Verificar se o usuário atual pode editar este usuário
        if ($currentUser->role !== 'super_admin' && $user->company_id !== $currentUser->company_id) {
            abort(403, 'Você não tem permissão para editar este usuário.');
        }
        
        if ($currentUser->role === 'super_admin') {
            // Super admin pode associar usuário a qualquer empresa
            $companies = Company::all();
        } else {
            // Admin só pode associar usuário à sua própria empresa
            $companies = Company::where('id', $currentUser->company_id)->get();
        }
        
        return view('users.edit', compact('user', 'companies'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, User $user): RedirectResponse
    {
        $currentUser = Auth::user();
        
        // Verificar se o usuário atual pode editar este usuário
        if ($currentUser->role !== 'super_admin' && $user->company_id !== $currentUser->company_id) {
            abort(403, 'Você não tem permissão para editar este usuário.');
        }
        
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email,' . $user->id,
            'password' => 'nullable|string|min:8|confirmed',
            'role' => ['required', Rule::in(['user', 'admin', 'super_admin'])],
            'company_id' => [
                Rule::requiredIf(function () use ($request) {
                    return in_array($request->role, ['user', 'admin']);
                }),
                'nullable',
                'exists:companies,id'
            ],
            'active' => 'boolean'
        ], [
            'password.confirmed' => 'A confirmação da senha não confere.',
            'company_id.required' => 'Campo obrigatório para user ou admin.'
        ]);

        // Verificar permissões de role
        if ($currentUser->role !== 'super_admin' && $validated['role'] === 'super_admin') {
            return back()->withErrors([
                'role' => 'Você não tem permissão para definir super administradores.'
            ])->withInput();
        }

        // Verificar permissões de empresa
        if ($currentUser->role !== 'super_admin') {
            // Admin só pode associar usuário à sua própria empresa
            $validated['company_id'] = $currentUser->company_id;
        }

        // Hash da senha apenas se foi fornecida
        if (!empty($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        } else {
            unset($validated['password']);
        }
        
        // Definir status ativo
        $validated['active'] = $validated['active'] ?? false;

        $user->update($validated);

        return redirect()->route('users.index')
            ->with('success', 'Usuário atualizado com sucesso!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $user): RedirectResponse
    {
        $currentUser = Auth::user();
        
        // Verificar se o usuário atual pode excluir este usuário
        if ($currentUser->role !== 'super_admin' && $user->company_id !== $currentUser->company_id) {
            abort(403, 'Você não tem permissão para excluir este usuário.');
        }
        
        // Não permitir que o usuário exclua a si mesmo
        if ($user->id === $currentUser->id) {
            return redirect()->route('users.index')
                ->with('error', 'Você não pode excluir sua própria conta.');
        }
        
        try {
            $user->delete();
            
            return redirect()->route('users.index')
                ->with('success', 'Usuário excluído com sucesso!');
        } catch (\Exception $e) {
            return redirect()->route('users.index')
                ->with('error', 'Erro ao excluir usuário. Verifique se não há registros relacionados.');
        }
    }

    /**
     * Toggle user active status.
     */
    public function toggleActive(User $user): RedirectResponse
    {
        $currentUser = Auth::user();
        
        // Verificar se o usuário atual pode alterar o status deste usuário
        if ($currentUser->role !== 'super_admin' && $user->company_id !== $currentUser->company_id) {
            abort(403, 'Você não tem permissão para alterar o status deste usuário.');
        }
        
        // Não permitir que o usuário desative a si mesmo
        if ($user->id === $currentUser->id) {
            return redirect()->route('users.index')
                ->with('error', 'Você não pode alterar o status da sua própria conta.');
        }
        
        $user->update(['active' => !$user->active]);
        
        $status = $user->active ? 'ativado' : 'desativado';
        
        return redirect()->route('users.index')
            ->with('success', "Usuário {$status} com sucesso!");
    }
}