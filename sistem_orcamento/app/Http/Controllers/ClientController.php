<?php

namespace App\Http\Controllers;

use App\Models\Client;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ClientController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): View
    {
        $user = auth()->guard('web')->user();
        
        if ($user->role === 'super_admin') {
            // Super admin pode ver todos os clientes
            $clients = Client::with('company')
                ->orderBy('fantasy_name')
                ->paginate(15);
        } else {
            // Admin e user veem apenas clientes da sua empresa
            $companyId = session('tenant_company_id');
            $clients = Client::where('company_id', $companyId)
                ->orderBy('fantasy_name')
                ->paginate(15);
        }
        
        return view('clients.index', compact('clients'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        $user = auth()->guard('web')->user();
        $companies = collect();
        
        if ($user->role === 'super_admin') {
            $companies = \App\Models\Company::orderBy('fantasy_name')->get();
        }
        
        return view('clients.create', compact('companies'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $user = auth()->guard('web')->user();
        
        $rules = [
            'fantasy_name' => 'nullable|string|max:255',
            'corporate_name' => 'nullable|string|max:255',
            'document_number' => 'required|string|max:18',
            'state_registration' => 'nullable|string|max:20',
            'phone' => 'required|string|max:20',
            'email' => 'required|email|max:255',
            'address' => 'required|string|max:500',
            'city' => 'required|string|max:255',
            'state' => 'required|string|max:2',
        ];
        
        // Super admin deve selecionar uma empresa
        if ($user->role === 'super_admin') {
            $rules['company_id'] = 'required|exists:companies,id';
        }
        
        $validated = $request->validate($rules);
        
        // Definir company_id para validação de unicidade
        $companyId = $user->role === 'super_admin' ? $validated['company_id'] : session('tenant_company_id');
        
        // Validação customizada: document_number único por empresa
        $existingClient = Client::where('document_number', $validated['document_number'])
                               ->where('company_id', $companyId)
                               ->first();
        
        if ($existingClient) {
            return back()->withErrors([
                'document_number' => 'Este CPF/CNPJ já está cadastrado nesta empresa.'
            ])->withInput();
        }

        // Validação customizada: pelo menos um dos campos deve estar preenchido
        if (empty($validated['fantasy_name']) && empty($validated['corporate_name'])) {
            return back()->withErrors([
                'fantasy_name' => 'Pelo menos um dos campos (Nome Fantasia ou Razão Social) deve ser preenchido.',
                'corporate_name' => 'Pelo menos um dos campos (Nome Fantasia ou Razão Social) deve ser preenchido.'
            ])->withInput();
        }

        // Definir company_id baseado no tipo de usuário
        if ($user->role === 'super_admin') {
            // Super admin usa o company_id selecionado no formulário
            $validated['company_id'] = $validated['company_id'];
        } else {
            // Outros usuários usam o company_id da sessão
            $validated['company_id'] = session('tenant_company_id');
        }
        
        Client::create($validated);

        return redirect()->route('clients.index')
            ->with('success', 'Cliente criado com sucesso!');
    }

    /**
     * Display the specified resource.
     */
    public function show(Client $client): View
    {
        $user = auth()->guard('web')->user();
        
        // Super admin pode ver qualquer cliente
        if ($user->role !== 'super_admin') {
            // Verificar se o cliente pertence à empresa do usuário
            if ($client->company_id !== session('tenant_company_id')) {
                abort(404);
            }
        }
        
        $client->load('contacts', 'budgets');
        return view('clients.show', compact('client'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Client $client): View
    {
        $user = auth()->guard('web')->user();
        
        // Super admin pode editar qualquer cliente
        if ($user->role !== 'super_admin') {
            // Verificar se o cliente pertence à empresa do usuário
            if ($client->company_id !== session('tenant_company_id')) {
                abort(404);
            }
        }
        
        $companies = collect();
        if ($user->role === 'super_admin') {
            $companies = \App\Models\Company::orderBy('fantasy_name')->get();
        }
        
        return view('clients.edit', compact('client', 'companies'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Client $client): RedirectResponse
    {
        $user = auth()->guard('web')->user();
        
        // Super admin pode atualizar qualquer cliente
        if ($user->role !== 'super_admin') {
            // Verificar se o cliente pertence à empresa do usuário
            if ($client->company_id !== session('tenant_company_id')) {
                abort(404);
            }
        }
        
        $rules = [
            'fantasy_name' => 'nullable|string|max:255',
            'corporate_name' => 'nullable|string|max:255',
            'document_number' => 'required|string|max:18',
            'state_registration' => 'nullable|string|max:20',
            'phone' => 'required|string|max:20',
            'email' => 'required|email|max:255',
            'address' => 'required|string|max:500',
            'city' => 'required|string|max:255',
            'state' => 'required|string|max:2',
        ];
        
        // Super admin pode alterar a empresa
        if ($user->role === 'super_admin') {
            $rules['company_id'] = 'required|exists:companies,id';
        }
        
        $validated = $request->validate($rules);
        
        // Definir company_id para validação de unicidade
        $companyId = $user->role === 'super_admin' && isset($validated['company_id']) ? $validated['company_id'] : $client->company_id;
        
        // Validação customizada: document_number único por empresa (exceto o próprio cliente)
        $existingClient = Client::where('document_number', $validated['document_number'])
                               ->where('company_id', $companyId)
                               ->where('id', '!=', $client->id)
                               ->first();
        
        if ($existingClient) {
            return back()->withErrors([
                'document_number' => 'Este CPF/CNPJ já está cadastrado nesta empresa.'
            ])->withInput();
        }

        // Validação customizada: pelo menos um dos campos deve estar preenchido
        if (empty($validated['fantasy_name']) && empty($validated['corporate_name'])) {
            return back()->withErrors([
                'fantasy_name' => 'Pelo menos um dos campos (Nome Fantasia ou Razão Social) deve ser preenchido.',
                'corporate_name' => 'Pelo menos um dos campos (Nome Fantasia ou Razão Social) deve ser preenchido.'
            ])->withInput();
        }

        $client->update($validated);

        return redirect()->route('clients.index')
            ->with('success', 'Cliente atualizado com sucesso!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Client $client): RedirectResponse
    {
        $user = auth()->guard('web')->user();
        
        // Super admin pode deletar qualquer cliente
        if ($user->role !== 'super_admin') {
            // Verificar se o cliente pertence à empresa do usuário
            if ($client->company_id !== session('tenant_company_id')) {
                abort(404);
            }
        }
        
        try {
            // Exclui o cliente (orçamentos e contatos serão excluídos em cascata)
            $client->delete();
            
            return redirect()->route('clients.index')
                ->with('success', 'Cliente excluído com sucesso! Todos os registros relacionados foram removidos.');
        } catch (\Exception $e) {
            return redirect()->route('clients.index')
                ->with('error', 'Erro ao excluir cliente. Verifique se não há registros relacionados.');
        }
    }

    /**
     * Get clients by company for AJAX requests
     */
    public function getClientsByCompany(Request $request)
    {
        $user = auth()->guard('web')->user();
        
        // Apenas super_admin pode acessar este endpoint
        if ($user->role !== 'super_admin') {
            return response()->json(['error' => 'Unauthorized'], 403);
        }
        
        $companyId = $request->get('company_id');
        
        if (!$companyId) {
            return response()->json(['error' => 'Company ID is required'], 400);
        }
        
        $clients = Client::where('company_id', $companyId)
            ->orderBy('fantasy_name')
            ->get()
            ->map(function ($client) {
                return [
                    'id' => $client->id,
                    'fantasy_name' => $client->fantasy_name,
                    'corporate_name' => $client->corporate_name,
                    'document_number' => $client->document_number,
                    'display_name' => $client->fantasy_name ?: $client->corporate_name
                ];
            });
        
        return response()->json($clients);
    }
}
