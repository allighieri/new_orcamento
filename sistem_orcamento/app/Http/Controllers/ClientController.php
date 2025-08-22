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
        $clients = Client::orderBy('fantasy_name')->paginate(15);
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

        Client::create($validated);

        return redirect()->route('clients.index')
            ->with('success', 'Cliente criado com sucesso!');
    }

    /**
     * Display the specified resource.
     */
    public function show(Client $client): View
    {
        $client->load('contacts', 'budgets');
        return view('clients.show', compact('client'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Client $client): View
    {
        return view('clients.edit', compact('client'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Client $client): RedirectResponse
    {
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
}
