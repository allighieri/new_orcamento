<?php

namespace App\Http\Controllers;

use App\Models\Contact;
use App\Models\Company;
use App\Models\Client;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\DB;

class ContactController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): View
    {
        $user = auth()->guard('web')->user();
        
        if ($user->role === 'super_admin') {
            // Super admin pode ver todos os contatos
            $query = Contact::with(['company', 'client']);
        } else {
            // Admin e user veem contatos da sua empresa ou de clientes da sua empresa
            $companyId = session('tenant_company_id');
            $query = Contact::where(function($q) use ($companyId) {
                $q->where('company_id', $companyId)
                  ->orWhereHas('client', function($subQ) use ($companyId) {
                      $subQ->where('company_id', $companyId);
                  });
            })
            ->with(['company', 'client']);
        }
        
        // Pesquisar por nome, CPF, telefone ou email do contato
        if ($request->has('search') && $request->search) {
            $searchTerm = $request->search;
            // Remove caracteres especiais para busca por documento
            $cleanSearchTerm = preg_replace('/[^0-9]/', '', $searchTerm);
            
            $query->where(function($q) use ($searchTerm, $cleanSearchTerm) {
                $q->where('name', 'LIKE', '%' . $searchTerm . '%')
                  ->orWhere('cpf', 'LIKE', '%' . $searchTerm . '%')
                  ->orWhere('email', 'LIKE', '%' . $searchTerm . '%')
                  ->orWhere('phone', 'LIKE', '%' . $searchTerm . '%');
                  
                // Se há números no termo de busca, busca também pelo documento sem formatação
                if (!empty($cleanSearchTerm)) {
                    $q->orWhere(\DB::raw('REPLACE(REPLACE(REPLACE(REPLACE(cpf, ".", ""), "-", ""), "/", ""), " ", "")'), 'LIKE', '%' . $cleanSearchTerm . '%');
                }
            });
        }
        
        $contacts = $query->orderBy('name')->paginate(10)->appends($request->query());
        
        // Se for requisição AJAX, retornar apenas a parte da tabela
        if ($request->ajax() || $request->has('ajax')) {
            return view('contacts.partials.table', compact('contacts'));
        }
            
        return view('contacts.index', compact('contacts'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request): View
    {
        $user = auth()->guard('web')->user();
        
        if ($user->role === 'super_admin') {
            // Super admin pode ver todas as empresas e clientes
            $companies = Company::orderBy('fantasy_name')->get();
            $clients = Client::with('company')->orderBy('fantasy_name')->get();
        } else {
            // Admin e user veem apenas da sua empresa
            $companyId = session('tenant_company_id');
            $companies = Company::where('id', $companyId)->orderBy('fantasy_name')->get();
            $clients = Client::where('company_id', $companyId)->orderBy('fantasy_name')->get();
        }
        
        // Pegar o client_id da query string se fornecido
        $selectedClientId = $request->query('client_id');
        
        return view('contacts.create', compact('companies', 'clients', 'selectedClientId'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $user = auth()->guard('web')->user();
        
        // Definir regras de validação baseadas na role do usuário
        $rules = [
            'name' => 'required|string|max:255',
            'cpf' => 'nullable|string|max:18',
            'phone' => 'required|string|min:14|max:15',
            'email' => 'required|email|max:255',
        ];
        
        // Validação condicional: empresa obrigatória se não tiver cliente, cliente obrigatório se não tiver empresa
        if (empty($request->client_id) && empty($request->company_id)) {
            return back()->withErrors([
                'client_id' => 'Você deve selecionar pelo menos um cliente ou uma empresa.',
                'company_id' => 'Você deve selecionar pelo menos um cliente ou uma empresa.'
            ])->withInput();
        }
        
        if (!empty($request->client_id)) {
            $rules['client_id'] = 'required|exists:clients,id';
        }
        
        // Apenas super_admin pode definir company_id
        if ($user->role === 'super_admin' && !empty($request->company_id)) {
            $rules['company_id'] = 'required|exists:companies,id';
        }
        
        $validated = $request->validate($rules);
        
        // Converter campos de texto para maiúsculo (exceto email)
        $fieldsToUppercase = ['name', 'cpf', 'phone'];
        foreach ($fieldsToUppercase as $field) {
            if (isset($validated[$field]) && !empty($validated[$field])) {
                $validated[$field] = strtoupper($validated[$field]);
            }
        }
        
        // Converter valores vazios para null
        if (empty($validated['client_id'])) {
            $validated['client_id'] = null;
        }
        if (empty($validated['company_id'])) {
            $validated['company_id'] = null;
        }
        
        // Validação customizada: CPF único por empresa quando preenchido
        if (!empty($validated['cpf'])) {
            $companyId = $user->role === 'super_admin' && !empty($validated['company_id']) ? $validated['company_id'] : session('tenant_company_id');
            
            $existingContact = Contact::where('cpf', $validated['cpf'])
                                    ->where('company_id', $companyId)
                                    ->first();
            
            if ($existingContact) {
                return back()->withErrors([
                    'cpf' => 'Este CPF/CNPJ já está cadastrado nesta empresa.'
                ])->withInput();
            }
        }

        // Para admin e user, sempre usar a empresa da sessão
        if ($user->role !== 'super_admin') {
            $validated['company_id'] = session('tenant_company_id');
        } else {
            // Para super_admin, se não informou company_id, usar da sessão
            if (empty($validated['company_id'])) {
                $validated['company_id'] = session('tenant_company_id');
            }
        }
        
        Contact::create($validated);

        return redirect()->route('contacts.index')
            ->with('success', 'Contato criado com sucesso!');
    }

    /**
     * Display the specified resource.
     */
    public function show(Contact $contact): View
    {
        $user = auth()->guard('web')->user();
        
        // Super admin pode ver qualquer contato
        if ($user->role !== 'super_admin') {
            $companyId = session('tenant_company_id');
            
            // Verificar se o contato pertence à empresa do usuário ou a um cliente da empresa
            $hasAccess = $contact->company_id === $companyId || 
                        ($contact->client && $contact->client->company_id === $companyId);
            
            if (!$hasAccess) {
                abort(404);
            }
        }
        
        $contact->load(['company', 'client']);
        return view('contacts.show', compact('contact'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Contact $contact): View
    {
        $user = auth()->guard('web')->user();
        
        // Super admin pode editar qualquer contato
        if ($user->role !== 'super_admin') {
            $companyId = session('tenant_company_id');
            
            // Verificar se o contato pertence à empresa do usuário ou a um cliente da empresa
            $hasAccess = $contact->company_id === $companyId || 
                        ($contact->client && $contact->client->company_id === $companyId);
            
            if (!$hasAccess) {
                abort(404);
            }
        }
        
        if ($user->role === 'super_admin') {
            // Super admin pode ver todas as empresas e clientes
            $companies = Company::orderBy('fantasy_name')->get();
            $clients = Client::with('company')->orderBy('fantasy_name')->get();
        } else {
            // Admin e user veem apenas da sua empresa
            $companyId = session('tenant_company_id');
            $companies = Company::where('id', $companyId)->orderBy('fantasy_name')->get();
            $clients = Client::where('company_id', $companyId)->orderBy('fantasy_name')->get();
        }
        
        return view('contacts.edit', compact('contact', 'companies', 'clients'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Contact $contact): RedirectResponse
    {
        $user = auth()->guard('web')->user();
        
        // Super admin pode atualizar qualquer contato
        if ($user->role !== 'super_admin') {
            $companyId = session('tenant_company_id');
            
            // Verificar se o contato pertence à empresa do usuário ou a um cliente da empresa
            $hasAccess = $contact->company_id === $companyId || 
                        ($contact->client && $contact->client->company_id === $companyId);
            
            if (!$hasAccess) {
                abort(404);
            }
        }
        
        $user = auth()->guard('web')->user();
        
        // Definir regras de validação baseadas na role do usuário
        $rules = [
            'name' => 'required|string|max:255',
            'cpf' => 'nullable|string|max:18',
            'phone' => 'required|string|min:14|max:15',
            'email' => 'required|email|max:255',
        ];
        
        // Validação condicional: empresa obrigatória se não tiver cliente, cliente obrigatório se não tiver empresa
        if (empty($request->client_id) && empty($request->company_id)) {
            return back()->withErrors([
                'client_id' => 'Você deve selecionar pelo menos um cliente ou uma empresa.',
                'company_id' => 'Você deve selecionar pelo menos um cliente ou uma empresa.'
            ])->withInput();
        }
        
        if (!empty($request->client_id)) {
            $rules['client_id'] = 'required|exists:clients,id';
        }
        
        // Apenas super_admin pode definir company_id
        if ($user->role === 'super_admin' && !empty($request->company_id)) {
            $rules['company_id'] = 'required|exists:companies,id';
        }
        
        $validated = $request->validate($rules);
        
        // Converter campos de texto para maiúsculo (exceto email)
        $fieldsToUppercase = ['name', 'cpf', 'phone'];
        foreach ($fieldsToUppercase as $field) {
            if (isset($validated[$field]) && !empty($validated[$field])) {
                $validated[$field] = strtoupper($validated[$field]);
            }
        }
        
        // Converter valores vazios para null
        if (empty($validated['client_id'])) {
            $validated['client_id'] = null;
        }
        if (empty($validated['company_id'])) {
            $validated['company_id'] = null;
        }
        
        // Validação customizada: CPF único por empresa quando preenchido (exceto o próprio contato)
        if (!empty($validated['cpf'])) {
            $companyId = $user->role === 'super_admin' && !empty($validated['company_id']) ? $validated['company_id'] : $contact->company_id;
            
            $existingContact = Contact::where('cpf', $validated['cpf'])
                                    ->where('company_id', $companyId)
                                    ->where('id', '!=', $contact->id)
                                    ->first();
            
            if ($existingContact) {
                return back()->withErrors([
                    'cpf' => 'Este CPF/CNPJ já está cadastrado nesta empresa.'
                ])->withInput();
            }
        }

        $contact->update($validated);

        return redirect()->route('contacts.index')
            ->with('success', 'Contato atualizado com sucesso!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Contact $contact): RedirectResponse
    {
        $user = auth()->guard('web')->user();
        
        // Verificar se o usuário tem permissão para excluir (apenas admin e super_admin)
        if ($user->role === 'user') {
            return redirect()->route('contacts.index')
                ->with('error', 'Você precisa de privilégios de Admin para excluir um Cliente!');
        }
        
        // Super admin pode excluir qualquer contato
        if ($user->role !== 'super_admin') {
            $companyId = session('tenant_company_id');
            
            // Verificar se o contato pertence à empresa do usuário ou a um cliente da empresa
            $hasAccess = $contact->company_id === $companyId || 
                        ($contact->client && $contact->client->company_id === $companyId);
            
            if (!$hasAccess) {
                abort(404);
            }
        }
        
        try {
            $clientId = $contact->client_id;
            $contact->delete();
            
            // Se veio da página de detalhes do cliente, redirecionar de volta
            if ($clientId && request()->has('from_client')) {
                return redirect()->route('clients.show', $clientId)
                    ->with('success', 'Contato excluído com sucesso!');
            }
            
            return redirect()->route('contacts.index')
                ->with('success', 'Contato excluído com sucesso!');
        } catch (\Exception $e) {
            $clientId = $contact->client_id;
            
            // Se veio da página de detalhes do cliente, redirecionar de volta
            if ($clientId && request()->has('from_client')) {
                return redirect()->route('clients.show', $clientId)
                    ->with('error', 'Erro ao excluir contato. Verifique se não há registros relacionados.');
            }
            
            return redirect()->route('contacts.index')
                ->with('error', 'Erro ao excluir contato. Verifique se não há registros relacionados.');
        }
    }
}
