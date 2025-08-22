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
        $contacts = Contact::with(['company', 'client'])
            ->orderBy('name')
            ->paginate(10);
            
        return view('contacts.index', compact('contacts'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        $companies = Company::orderBy('fantasy_name')->get();
        $clients = Client::orderBy('fantasy_name')->get();
        
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

        Contact::create($validated);

        return redirect()->route('contacts.index')
            ->with('success', 'Contato criado com sucesso!');
    }

    /**
     * Display the specified resource.
     */
    public function show(Contact $contact): View
    {
        $contact->load(['company', 'client']);
        return view('contacts.show', compact('contact'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Contact $contact): View
    {
        $companies = Company::orderBy('fantasy_name')->get();
        $clients = Client::orderBy('fantasy_name')->get();
        
        return view('contacts.edit', compact('contact', 'companies', 'clients'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Contact $contact): RedirectResponse
    {
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
