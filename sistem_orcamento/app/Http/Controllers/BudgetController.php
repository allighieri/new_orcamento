<?php

namespace App\Http\Controllers;

use App\Models\Budget;
use App\Models\BudgetItem;
use App\Models\Client;
use App\Models\Company;
use App\Models\Product;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class BudgetController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $user = auth()->guard('web')->user();
        $companyId = session('tenant_company_id');
        
        if ($user->role === 'super_admin') {
            // Super admin pode ver todos os orçamentos
            $query = Budget::with(['client', 'company'])
                ->orderBy('created_at', 'desc');
        } else {
            // Admin e user veem apenas orçamentos da sua empresa
            $query = Budget::where('company_id', $companyId)
                ->with(['client', 'company'])
                ->orderBy('created_at', 'desc');
        }
        
        // Filtrar por cliente se especificado
        if ($request->has('client') && $request->client) {
            $query->where('client_id', $request->client);
        }
        
        $budgets = $query->paginate(10);
        
        // Buscar dados do cliente para exibir no título se filtrado
        $client = null;
        if ($request->has('client') && $request->client) {
            if ($user->role === 'super_admin') {
                $client = Client::find($request->client);
            } else {
                $client = Client::where('id', $request->client)
                    ->where('company_id', $companyId)
                    ->first();
            }
        }
        
        return view('budgets.index', compact('budgets', 'client'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        // $user = auth()->user();
        $user = auth()->guard('web')->user();
        
        if ($user->role === 'super_admin') {
            // Super admin pode ver todos os clientes, empresas e produtos
            $clients = Client::orderBy('fantasy_name')->get();
            $companies = Company::orderBy('fantasy_name')->get();
            $products = Product::with(['category'])->orderBy('name')->get();
        } else {
            // Admin e user veem apenas da sua empresa
            $companyId = session('tenant_company_id');
            $clients = Client::where('company_id', $companyId)->orderBy('fantasy_name')->get();
            $companies = Company::where('id', $companyId)->orderBy('fantasy_name')->get();
            $products = Product::where('company_id', $companyId)->with(['category'])->orderBy('name')->get();
        }
        
        return view('budgets.create', compact('clients', 'companies', 'products'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        error_log('BUDGET STORE METHOD CALLED - ' . date('Y-m-d H:i:s'));
\Illuminate\Support\Facades\Log::info('Budget store method called', ['request_data' => $request->all()]);
        
        // Debug: verificar se products é um array
        if ($request->has('products')) {
            \Illuminate\Support\Facades\Log::info('Products data type', ['type' => gettype($request->products), 'is_array' => is_array($request->products)]);
        }
        
        // Processar dados monetários antes da validação
        if ($request->has('total_discount')) {
            $request->merge([
                'total_discount' => $this->convertMoneyToFloat($request->total_discount)
            ]);
        }
        
        if ($request->has('products')) {
            $products = $request->products;
            foreach ($products as $index => $product) {
                if (isset($product['unit_price'])) {
                    $products[$index]['unit_price'] = $this->convertMoneyToFloat($product['unit_price']);
                }
            }
            $request->merge(['products' => $products]);
        }
        
\Illuminate\Support\Facades\Log::info('Before validation', ['processed_data' => $request->all()]);
        
        // Validação condicional: empresa obrigatória se não tiver cliente, cliente obrigatório se não tiver empresa
        $rules = [
            'issue_date' => 'required|date',
            'valid_until' => 'required|date|after_or_equal:issue_date',
            'total_discount' => 'nullable|numeric|min:0',
            'observations' => 'nullable|string|max:1000',
            'products' => 'required|array|min:1',
            'products.*.product_id' => 'required|exists:products,id',
            'products.*.quantity' => 'required|numeric|min:0.01',
            'products.*.unit_price' => 'required|numeric|min:0',
            'products.*.description' => 'nullable|string',
            'products.*.discount_percentage' => 'nullable|numeric|min:0|max:100'
        ];
        
        $user = auth()->guard('web')->user();
        
        // Aplicar validação condicional baseada no papel do usuário
        if ($user->role === 'super_admin') {
            // Super admin deve preencher ambos os campos
            if (empty($request->client_id) || empty($request->company_id)) {
                $errors = [];
                if (empty($request->client_id)) {
                    $errors['client_id'] = 'Selecione um cliente';
                }
                if (empty($request->company_id)) {
                    $errors['company_id'] = 'Selecione uma empresa';
                }
                return back()->withErrors($errors)->withInput();
            }
            $rules['client_id'] = 'required|exists:clients,id';
            $rules['company_id'] = 'required|exists:companies,id';
        } else {
            // Admin e user devem preencher cliente
            if (empty($request->client_id)) {
                return back()->withErrors([
                    'client_id' => 'Selecione um cliente.'
                ])->withInput();
            }
            $rules['client_id'] = 'required|exists:clients,id';
        }
        
        $request->validate($rules);
        
        // Converter valores vazios para null
        $clientId = empty($request->client_id) ? null : $request->client_id;
        // Definir empresa: super_admin usa a selecionada, outros usam automaticamente
        $companyId = ($user->role === 'super_admin') ? $request->company_id : session('tenant_company_id');

        DB::beginTransaction();
        try {
            // Gerar número do orçamento no formato 0000-0/YYYY (incluindo ID da empresa)
            $year = date('Y');
            $lastBudget = Budget::where('company_id', $companyId)
                               ->whereYear('created_at', $year)
                               ->orderBy('id', 'desc')
                               ->first();
            $nextNumber = $lastBudget ? (intval(substr($lastBudget->number, 0, 4)) + 1) : 1;
            $budgetNumber = str_pad($nextNumber, 4, '0', STR_PAD_LEFT) . '-' . $companyId . '/' . $year;

            // Criar orçamento
            $budget = Budget::create([
                'number' => $budgetNumber,
                'client_id' => $clientId,
                'company_id' => $companyId,
                'issue_date' => $request->issue_date,
                'valid_until' => $request->valid_until,
                'status' => 'Pendente',
                'total_discount' => $request->total_discount ?? 0,
                'observations' => $request->observations,
                'total_amount' => 0, // Será calculado depois
                'final_amount' => 0 // Será calculado depois
            ]);

            // Criar itens do orçamento
            $totalAmount = 0;
            foreach ($request->products as $productData) {
                $itemTotal = $productData['quantity'] * $productData['unit_price'];
                $discountAmount = $itemTotal * (($productData['discount_percentage'] ?? 0) / 100);
                $totalPrice = $itemTotal - $discountAmount;
                $totalAmount += $totalPrice;

                BudgetItem::create([
                    'budget_id' => $budget->id,
                    'product_id' => $productData['product_id'],
                    'quantity' => $productData['quantity'],
                    'unit_price' => $productData['unit_price'],
                    'discount_percentage' => $productData['discount_percentage'] ?? 0,
                    'total_price' => $totalPrice,
                    'description' => $productData['description'] ?? ''
                ]);
            }

            // Calcular valor final
            $finalAmount = $totalAmount - ($request->total_discount ?? 0);

            // Atualizar totais do orçamento
            $budget->update([
                'total_amount' => $totalAmount,
                'final_amount' => $finalAmount
            ]);

            \Illuminate\Support\Facades\Log::info('Budget created successfully', ['budget_id' => $budget->id, 'budget_number' => $budget->number]);
            
            DB::commit();
            return redirect()->route('budgets.show', $budget)->with('success', 'Orçamento criado com sucesso!');
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Error creating budget', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            
            DB::rollback();
            return back()->withInput()->with('error', 'Erro ao criar orçamento: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Budget $budget)
    {
        $user = auth()->guard('web')->user();
        
        // Super admin pode ver qualquer orçamento
        if ($user->role !== 'super_admin') {
            // Verificar se o orçamento pertence à empresa do usuário
            if ($budget->company_id !== session('tenant_company_id')) {
                abort(404);
            }
        }
        
        $budget->load(['client', 'company', 'items.product']);
        return view('budgets.show', compact('budget'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Budget $budget)
    {
        $user = auth()->guard('web')->user();
        
        // Super admin pode editar qualquer orçamento
        if ($user->role !== 'super_admin') {
            // Verificar se o orçamento pertence à empresa do usuário
            if ($budget->company_id !== session('tenant_company_id')) {
                abort(404);
            }
        }
        
        if ($user->role === 'super_admin') {
            // Super admin vê apenas clientes da empresa do orçamento sendo editado
            $clients = Client::where('company_id', $budget->company_id)->orderBy('fantasy_name')->get();
            $companies = Company::orderBy('fantasy_name')->get();
            $products = Product::with(['category'])->orderBy('name')->get();
        } else {
            // Admin e user veem apenas da sua empresa
            $companyId = session('tenant_company_id');
            $clients = Client::where('company_id', $companyId)->orderBy('fantasy_name')->get();
            $companies = Company::where('id', $companyId)->orderBy('fantasy_name')->get();
            $products = Product::where('company_id', $companyId)->with(['category'])->orderBy('name')->get();
        }
        
        $budget->load(['items.product']);
        
        return view('budgets.edit', compact('budget', 'clients', 'companies', 'products'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Budget $budget)
    {
        $user = auth()->guard('web')->user();
        
        // Super admin pode atualizar qualquer orçamento
        if ($user->role !== 'super_admin') {
            // Verificar se o orçamento pertence à empresa do usuário
            if ($budget->company_id !== session('tenant_company_id')) {
                abort(404);
            }
        }
        
        // Processar dados monetários antes da validação
        if ($request->has('total_discount')) {
            $request->merge([
                'total_discount' => $this->convertMoneyToFloat($request->total_discount)
            ]);
        }
        
        if ($request->has('items')) {
            $items = $request->items;
            foreach ($items as $index => $item) {
                if (isset($item['unit_price'])) {
                    $items[$index]['unit_price'] = $this->convertMoneyToFloat($item['unit_price']);
                }
            }
            $request->merge(['items' => $items]);
        }
        
        // Validação condicional: empresa obrigatória se não tiver cliente, cliente obrigatório se não tiver empresa
        $rules = [
            'issue_date' => 'required|date',
            'valid_until' => 'required|date|after_or_equal:issue_date',
            'total_discount' => 'nullable|numeric|min:0',
            'observations' => 'nullable|string|max:1000',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.unit_price' => 'required|numeric|min:0',
            'items.*.description' => 'nullable|string',
            'items.*.discount_percentage' => 'nullable|numeric|min:0|max:100'
        ];

        // Aplicar validação condicional baseada no papel do usuário
        if ($user->role === 'super_admin') {
            // Super admin deve preencher ambos os campos
            if (empty($request->client_id) || empty($request->company_id)) {
                $errors = [];
                if (empty($request->client_id)) {
                    $errors['client_id'] = 'Selecione um cliente';
                }
                if (empty($request->company_id)) {
                    $errors['company_id'] = 'Selecione uma empresa';
                }
                return back()->withErrors($errors)->withInput();
            }
            $rules['client_id'] = 'required|exists:clients,id';
            $rules['company_id'] = 'required|exists:companies,id';
           
        } else {
            // Admin e user devem preencher cliente
            if (empty($request->client_id)) {
                return back()->withErrors([
                    'client_id' => 'Selecione um cliente.'
                ])->withInput();
            }
            $rules['client_id'] = 'required|exists:clients,id';
        }
        
        $request->validate($rules);
        
        // Converter valores vazios para null
        $clientId = empty($request->client_id) ? null : $request->client_id;
        // Definir empresa: super_admin usa a selecionada, outros usam automaticamente
        $companyId = ($user->role === 'super_admin') ? $request->company_id : session('tenant_company_id');

        DB::beginTransaction();
        try {
            // Atualizar orçamento
            $budget->update([
                'client_id' => $clientId,
                'company_id' => $companyId,
                'issue_date' => $request->issue_date,
                'valid_until' => $request->valid_until,
                'status' => 'Pendente',
                'total_discount' => $request->total_discount ?? 0,
                'observations' => $request->observations
            ]);

            // Remover itens antigos
            $budget->items()->delete();

            // Criar novos itens
            $totalAmount = 0;
            foreach ($request->items as $itemData) {
                $itemTotal = $itemData['quantity'] * $itemData['unit_price'];
                $discountAmount = $itemTotal * (($itemData['discount_percentage'] ?? 0) / 100);
                $totalPrice = $itemTotal - $discountAmount;
                $totalAmount += $totalPrice;

                BudgetItem::create([
                    'budget_id' => $budget->id,
                    'product_id' => $itemData['product_id'],
                    'quantity' => $itemData['quantity'],
                    'unit_price' => $itemData['unit_price'],
                    'discount_percentage' => $itemData['discount_percentage'] ?? 0,
                    'total_price' => $totalPrice,
                    'description' => $itemData['description'] ?? ''
                ]);
            }

            // Calcular valor final
            $finalAmount = $totalAmount - ($request->total_discount ?? 0);

            // Atualizar totais do orçamento
            $budget->update([
                'total_amount' => $totalAmount,
                'final_amount' => $finalAmount
            ]);

            DB::commit();
            return redirect()->route('budgets.show', $budget)->with('success', 'Orçamento atualizado com sucesso!');
        } catch (\Exception $e) {
            DB::rollback();
            return back()->withInput()->with('error', 'Erro ao atualizar orçamento: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Budget $budget)
    {
        $user = auth()->guard('web')->user();
        
        // Super admin pode excluir qualquer orçamento
        if ($user->role !== 'super_admin') {
            // Verificar se o orçamento pertence à empresa do usuário
            if ($budget->company_id !== session('tenant_company_id')) {
                abort(404);
            }
        }
        
        try {
            // Gerar nome do arquivo PDF usando o mesmo padrão da função generatePdf
            if(empty($budget->client->corporate_name)){
                $filename = Str::slug($budget->client->fantasy_name) . '-' . 'orcamento-' . str_replace('/', '-', $budget->number) . '.pdf';
            } else {
                $filename = Str::slug($budget->client->corporate_name) . '-' . 'orcamento-' . str_replace('/', '-', $budget->number) . '.pdf';
            }
            $filePath = storage_path('app/public/pdfs/' . $filename);
            
            // Verificar se o arquivo PDF existe e excluí-lo
            if (file_exists($filePath)) {
                unlink($filePath);
            }
            
            // Excluir o orçamento do banco de dados
            $budget->delete();
            
            return redirect()->route('budgets.index')->with('success', 'Orçamento e arquivo PDF excluídos com sucesso!');
        } catch (\Exception $e) {
            return redirect()->route('budgets.index')->with('error', 'Erro ao excluir orçamento: ' . $e->getMessage());
        }
    }
    
    /**
     * Converte valor monetário formatado para float
     */
    private function convertMoneyToFloat($value)
    {
        if (is_null($value) || $value === '') {
            return 0;
        }
        
        // Remove pontos (separadores de milhares) e substitui vírgula por ponto
        $value = str_replace('.', '', $value);
        $value = str_replace(',', '.', $value);
        
        return (float) $value;
    }
    
    /**
     * Generate PDF for the budget.
     */
    public function generatePdf(Budget $budget)
    {
        $user = auth()->guard('web')->user();
        
        // Super admin pode gerar PDF de qualquer orçamento
        if ($user->role !== 'super_admin') {
            // Verificar se o orçamento pertence à empresa do usuário
            if ($budget->company_id !== session('tenant_company_id')) {
                abort(404);
            }
        }
        
        $budget->load(['client', 'items.product']);
        
        $pdf = Pdf::loadView('pdf.budget', compact('budget'));
        $pdf->setPaper('A4', 'portrait');

        if(empty($budget->client->corporate_name)){
             $filename = Str::slug($budget->client->fantasy_name) . '-' . 'orcamento-' . str_replace('/', '-', $budget->number) . '.pdf';
        } else {
            $filename = Str::slug($budget->client->corporate_name) . '-' . 'orcamento-' . str_replace('/', '-', $budget->number) . '.pdf';
        }

        //return $pdf->download($filename);
        
        // Salvar o PDF no servidor
        $filePath = 'pdfs/' . $filename;
        $fullPath = storage_path('app/public/' . $filePath);
        
        // Criar o diretório se não existir
        $directory = dirname($fullPath);
        if (!file_exists($directory)) {
            mkdir($directory, 0755, true);
        }
        
        // Salvar o arquivo
        file_put_contents($fullPath, $pdf->output());
        
        // Verificar se a requisição é AJAX
        if (request()->ajax()) {
            // Para requisições AJAX, retornar JSON com URL para abrir
            $publicUrl = asset('storage/' . $filePath);
            return response()->json([
                'success' => true,
                'message' => 'PDF gerado e salvo com sucesso!',
                'filename' => $filename,
                'path' => $filePath,
                'full_path' => $fullPath,
                'size' => filesize($fullPath),
                'url' => $publicUrl
            ]);
        } else {
            // Para requisições normais, abrir no navegador
            return $pdf->stream($filename);
        }
    }
}
