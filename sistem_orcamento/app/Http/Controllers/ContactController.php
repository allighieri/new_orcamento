<?php

namespace App\Http\Controllers;

use App\Models\Contact;
use App\Models\Company;
use App\Models\Client;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ContactController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): View
    {
        $companyId = session('tenant_company_id');
        $contacts = Contact::where('company_id', $companyId)
            ->with(['company', 'client'])
            ->orderBy('name')
            ->paginate(10);
            
        return view('contacts.index', compact('contacts'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        $companyId = session('tenant_company_id');
        $companies = Company::where('id', $companyId)->orderBy('fantasy_name')->get();
        $clients = Client::where('company_id', $companyId)->orderBy('fantasy_name')->get();
        
        return view('contacts.create', compact('companies', 'clients'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'cpf' => 'nullable|string|max:14',
            'phone' => 'required|string|max:20',
            'email' => 'required|email|max:255',
            'company_id' => 'nullable|exists:companies,id',
            'client_id' => 'nullable|exists:clients,id',
        ]);

        // Garantir que o contato seja criado para a empresa do usuário
        $validated['company_id'] = session('tenant_company_id');
        Contact::create($validated);

        return redirect()->route('contacts.index')
            ->with('success', 'Contato criado com sucesso!');
    }

    /**
     * Display the specified resource.
     */
    public function show(Contact $contact): View
    {
        // Verificar se o contato pertence à empresa do usuário
        if ($contact->company_id !== session('tenant_company_id')) {
            abort(404);
        }
        
        $contact->load(['company', 'client']);
        return view('contacts.show', compact('contact'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Contact $contact): View
    {
        // Verificar se o contato pertence à empresa do usuário
        if ($contact->company_id !== session('tenant_company_id')) {
            abort(404);
        }
        
        $companyId = session('tenant_company_id');
        $companies = Company::where('id', $companyId)->orderBy('fantasy_name')->get();
        $clients = Client::where('company_id', $companyId)->orderBy('fantasy_name')->get();
        
        return view('contacts.edit', compact('contact', 'companies', 'clients'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Contact $contact): RedirectResponse
    {
        // Verificar se o contato pertence à empresa do usuário
        if ($contact->company_id !== session('tenant_company_id')) {
            abort(404);
        }
        
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'cpf' => 'nullable|string|max:14',
            'phone' => 'required|string|max:20',
            'email' => 'required|email|max:255',
            'company_id' => 'nullable|exists:companies,id',
            'client_id' => 'nullable|exists:clients,id',
        ]);

        $contact->update($validated);

        return redirect()->route('contacts.index')
            ->with('success', 'Contato atualizado com sucesso!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Contact $contact): RedirectResponse
    {
        // Verificar se o contato pertence à empresa do usuário
        if ($contact->company_id !== session('tenant_company_id')) {
            abort(404);
        }
        
        try {
            $contact->delete();
            return redirect()->route('contacts.index')
                ->with('success', 'Contato excluído com sucesso!');
        } catch (\Exception $e) {
            return redirect()->route('contacts.index')
                ->with('error', 'Erro ao excluir contato.');
        }
    }
}
