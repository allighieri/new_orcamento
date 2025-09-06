<?php

namespace App\Http\Controllers;

use App\Models\Company;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

class CompanyController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $user = auth()->guard('web')->user();
        
        // Se for admin, redireciona para a visualização da sua própria empresa
        if ($user->role === 'admin' && $user->company_id) {
            $company = Company::find($user->company_id);
            if ($company) {
                return redirect()->route('companies.show', $company);
            }
        }
        
        // Super admin vê a listagem completa
        $query = Company::query();
        
        // Pesquisar por nome corporativo, fantasia ou CNPJ da empresa
        if ($request->has('search') && $request->search) {
            $searchTerm = $request->search;
            // Remove caracteres especiais para busca por documento
            $cleanSearchTerm = preg_replace('/[^0-9]/', '', $searchTerm);
            
            $query->where(function($q) use ($searchTerm, $cleanSearchTerm) {
                $q->where('corporate_name', 'LIKE', '%' . $searchTerm . '%')
                  ->orWhere('fantasy_name', 'LIKE', '%' . $searchTerm . '%')
                  ->orWhere('document_number', 'LIKE', '%' . $searchTerm . '%');
                  
                // Se há números no termo de busca, busca também pelo documento sem formatação
                if (!empty($cleanSearchTerm)) {
                    $q->orWhere(\DB::raw('REPLACE(REPLACE(REPLACE(REPLACE(document_number, ".", ""), "-", ""), "/", ""), " ", "")'), 'LIKE', '%' . $cleanSearchTerm . '%');
                }
            });
        }
        
        $companies = $query->paginate(10)->appends($request->query());
        return view('companies.index', compact('companies'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
         return view('companies.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'corporate_name' => 'nullable|string|max:255',
            'fantasy_name' => 'nullable|string|max:255',
            'document_number' => 'required|string|max:18|unique:companies,document_number',
            'state_registration' => 'nullable|string|max:20',
            'phone' => 'required|string|min:14|max:15',
            'email' => 'required|email|max:255',
            'address' => 'required|string|max:500',
            'district' => 'nullable|string|max:255',
            'city' => 'required|string|max:255',
            'state' => 'required|string|max:2',
            'cep' => 'nullable|string|max:10',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        // Validação customizada: pelo menos um dos campos deve estar preenchido
        if (empty($validated['corporate_name']) && empty($validated['fantasy_name'])) {
            return back()->withErrors([
                'corporate_name' => 'Pelo menos um dos campos (Razão Social ou Nome Fantasia) deve ser preenchido.',
                'fantasy_name' => 'Pelo menos um dos campos (Razão Social ou Nome Fantasia) deve ser preenchido.'
            ])->withInput();
        }

        // Processar upload da logo
        if ($request->hasFile('logo')) {
            $logoPath = $request->file('logo')->store('logos', 'public');
            $validated['logo'] = $logoPath;
        }

        Company::create($validated);

        return redirect()->route('companies.index')
            ->with('success', 'Empresa cadastrada com sucesso!');
    }

    /**
     * Display the specified resource.
     */
    public function show(Company $company): View
    {
        $company->load('contacts', 'users');
        return view('companies.show', compact('company'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Company $company): View
    {
        return view('companies.edit', compact('company'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Company $company): RedirectResponse
    {
        $validated = $request->validate([
            'corporate_name' => 'nullable|string|max:255',
            'fantasy_name' => 'nullable|string|max:255',
            'document_number' => 'required|string|max:18|unique:companies,document_number,' . $company->id,
            'state_registration' => 'nullable|string|max:20',
            'phone' => 'required|string|min:14|max:15',
            'email' => 'required|email|max:255',
            'address' => 'required|string|max:500',
            'district' => 'nullable|string|max:255',
            'city' => 'required|string|max:255',
            'state' => 'required|string|max:2',
            'cep' => 'nullable|string|max:10',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        // Validação customizada: pelo menos um dos campos deve estar preenchido
        if (empty($validated['corporate_name']) && empty($validated['fantasy_name'])) {
            return back()->withErrors([
                'corporate_name' => 'Pelo menos um dos campos (Razão Social ou Nome Fantasia) deve ser preenchido.',
                'fantasy_name' => 'Pelo menos um dos campos (Razão Social ou Nome Fantasia) deve ser preenchido.'
            ])->withInput();
        }

        // Processar upload da nova logo
        if ($request->hasFile('logo')) {
            // Excluir logo anterior se existir
            if ($company->logo && Storage::disk('public')->exists($company->logo)) {
                Storage::disk('public')->delete($company->logo);
            }
            
            $logoPath = $request->file('logo')->store('logos', 'public');
            $validated['logo'] = $logoPath;
        }

        $company->update($validated);

        return redirect()->route('companies.index')
            ->with('success', 'Empresa atualizada com sucesso!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Company $company): RedirectResponse
    {
        try {
            // Desassocia os contatos da empresa (define company_id como null)
            $company->contacts()->update(['company_id' => null]);
            
            // Exclui a empresa
            $company->delete();
            
            return redirect()->route('companies.index')
                ->with('success', 'Empresa excluída com sucesso! Os contatos foram preservados.');
        } catch (\Exception $e) {
            return redirect()->route('companies.index')
                ->with('error', 'Erro ao excluir empresa. Verifique se não há registros relacionados.');
        }
    }
}
