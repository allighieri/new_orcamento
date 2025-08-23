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
        return view('clients.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'fantasy_name' => 'nullable|string|max:255',
            'corporate_name' => 'nullable|string|max:255',
            'document_number' => 'required|string|max:18|unique:clients,document_number',
            'state_registration' => 'nullable|string|max:20',
            'phone' => 'required|string|max:20',
            'email' => 'required|email|max:255',
            'address' => 'required|string|max:500',
            'city' => 'required|string|max:255',
            'state' => 'required|string|max:2',
        ]);

        // Validação customizada: pelo menos um dos campos deve estar preenchido
        if (empty($validated['fantasy_name']) && empty($validated['corporate_name'])) {
            return back()->withErrors([
                'fantasy_name' => 'Pelo menos um dos campos (Nome Fantasia ou Razão Social) deve ser preenchido.',
                'corporate_name' => 'Pelo menos um dos campos (Nome Fantasia ou Razão Social) deve ser preenchido.'
            ])->withInput();
        }

        $validated['company_id'] = session('tenant_company_id');
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
        
        return view('clients.edit', compact('client'));
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
        
        $validated = $request->validate([
            'fantasy_name' => 'nullable|string|max:255',
            'corporate_name' => 'nullable|string|max:255',
            'document_number' => 'required|string|max:18|unique:clients,document_number,' . $client->id,
            'state_registration' => 'nullable|string|max:20',
            'phone' => 'required|string|max:20',
            'email' => 'required|email|max:255',
            'address' => 'required|string|max:500',
            'city' => 'required|string|max:255',
            'state' => 'required|string|max:2',
        ]);

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
            // Desassocia os contatos do cliente (define client_id como null)
            $client->contacts()->update(['client_id' => null]);
            
            // Exclui o cliente
            $client->delete();
            
            return redirect()->route('clients.index')
                ->with('success', 'Cliente excluído com sucesso! Os contatos foram preservados.');
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
