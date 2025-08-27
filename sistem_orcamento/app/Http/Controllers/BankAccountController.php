<?php

namespace App\Http\Controllers;

use App\Models\BankAccount;
use App\Models\Compe;
use App\Models\Company;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class BankAccountController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): View
    {
        $user = auth()->guard('web')->user();
        
        if ($user->role === 'super_admin') {
            // Super admin pode ver todas as contas bancárias
            $bankAccounts = BankAccount::with(['company', 'compe'])
                ->orderBy('description')
                ->paginate(10);
        } else {
            // Admin e user veem apenas contas da sua empresa
            $companyId = session('tenant_company_id');
            $bankAccounts = BankAccount::where('company_id', $companyId)
                ->with(['company', 'compe'])
                ->orderBy('description')
                ->paginate(10);
        }
        
        return view('bank-accounts.index', compact('bankAccounts'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        $user = auth()->guard('web')->user();
        $companies = collect();
        
        if ($user->role === 'super_admin') {
            $companies = Company::orderBy('fantasy_name')->get();
        }
        
        $compes = Compe::active()->orderBy('bank_name')->get();
        
        return view('bank-accounts.create', compact('companies', 'compes'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $user = auth()->guard('web')->user();
        
        $rules = [
            'type' => 'required|in:PIX,Conta',
            'description' => 'required|string|max:255',
            'compe_id' => 'nullable|exists:compes,id',
            'branch' => 'nullable|string|max:10',
            'account' => 'nullable|string|max:20',
            'active' => 'boolean'
        ];
        
        // Apenas super_admin pode definir company_id
        if ($user->role === 'super_admin') {
            $rules['company_id'] = 'required|exists:companies,id';
        }
        
        $validated = $request->validate($rules);
        
        // Definir company_id baseado no tipo de usuário
        if ($user->role === 'super_admin') {
            $validated['company_id'] = $validated['company_id'];
        } else {
            $validated['company_id'] = session('tenant_company_id');
        }
        
        // Validação condicional para tipo Conta
        if ($validated['type'] === 'Conta') {
            if (empty($validated['compe_id']) || empty($validated['branch']) || empty($validated['account'])) {
                return back()->withErrors([
                    'compe_id' => 'Para contas bancárias, o banco, agência e conta são obrigatórios.',
                    'branch' => 'Para contas bancárias, o banco, agência e conta são obrigatórios.',
                    'account' => 'Para contas bancárias, o banco, agência e conta são obrigatórios.'
                ])->withInput();
            }
        }
        
        BankAccount::create($validated);
        
        return redirect()->route('bank-accounts.index')
            ->with('success', 'Conta bancária criada com sucesso!');
    }

    /**
     * Display the specified resource.
     */
    public function show(BankAccount $bankAccount): View
    {
        $user = auth()->guard('web')->user();
        
        // Super admin pode ver qualquer conta
        if ($user->role !== 'super_admin') {
            // Verificar se a conta pertence à empresa do usuário
            if ($bankAccount->company_id !== session('tenant_company_id')) {
                abort(404);
            }
        }
        
        $bankAccount->load(['company', 'compe']);
        return view('bank-accounts.show', compact('bankAccount'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(BankAccount $bankAccount): View
    {
        $user = auth()->guard('web')->user();
        
        // Super admin pode editar qualquer conta
        if ($user->role !== 'super_admin') {
            // Verificar se a conta pertence à empresa do usuário
            if ($bankAccount->company_id !== session('tenant_company_id')) {
                abort(404);
            }
        }
        
        $companies = collect();
        if ($user->role === 'super_admin') {
            $companies = Company::orderBy('fantasy_name')->get();
        }
        
        $compes = Compe::active()->orderBy('bank_name')->get();
        
        return view('bank-accounts.edit', compact('bankAccount', 'companies', 'compes'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, BankAccount $bankAccount): RedirectResponse
    {
        $user = auth()->guard('web')->user();
        
        // Super admin pode atualizar qualquer conta
        if ($user->role !== 'super_admin') {
            // Verificar se a conta pertence à empresa do usuário
            if ($bankAccount->company_id !== session('tenant_company_id')) {
                abort(404);
            }
        }
        
        $rules = [
            'type' => 'required|in:PIX,Conta',
            'description' => 'required|string|max:255',
            'compe_id' => 'nullable|exists:compes,id',
            'branch' => 'nullable|string|max:10',
            'account' => 'nullable|string|max:20',
            'active' => 'boolean'
        ];
        
        // Apenas super_admin pode definir company_id
        if ($user->role === 'super_admin') {
            $rules['company_id'] = 'required|exists:companies,id';
        }
        
        $validated = $request->validate($rules);
        
        // Validação condicional para tipo Conta
        if ($validated['type'] === 'Conta') {
            if (empty($validated['compe_id']) || empty($validated['branch']) || empty($validated['account'])) {
                return back()->withErrors([
                    'compe_id' => 'Para contas bancárias, o banco, agência e conta são obrigatórios.',
                    'branch' => 'Para contas bancárias, o banco, agência e conta são obrigatórios.',
                    'account' => 'Para contas bancárias, o banco, agência e conta são obrigatórios.'
                ])->withInput();
            }
        }
        
        $bankAccount->update($validated);
        
        return redirect()->route('bank-accounts.index')
            ->with('success', 'Conta bancária atualizada com sucesso!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(BankAccount $bankAccount): RedirectResponse
    {
        $user = auth()->guard('web')->user();
        
        // Super admin pode excluir qualquer conta
        if ($user->role !== 'super_admin') {
            // Verificar se a conta pertence à empresa do usuário
            if ($bankAccount->company_id !== session('tenant_company_id')) {
                abort(404);
            }
        }
        
        try {
            $bankAccount->delete();
            
            return redirect()->route('bank-accounts.index')
                ->with('success', 'Conta bancária excluída com sucesso!');
        } catch (\Exception $e) {
            return redirect()->route('bank-accounts.index')
                ->with('error', 'Erro ao excluir conta bancária. Verifique se não há registros relacionados.');
        }
    }
}
