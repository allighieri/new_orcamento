<?php

namespace App\Http\Controllers;

use App\Models\Budget;
use App\Models\BudgetItem;
use App\Models\Client;
use App\Models\Company;
use App\Models\Product;
use App\Models\Contact;
use App\Models\PaymentMethod;
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
            $query = Budget::with(['client', 'company', 'pdfFiles'])
                ->orderBy('created_at', 'desc');
        } else {
            // Admin e user veem apenas orçamentos da sua empresa
            $query = Budget::where('company_id', $companyId)
                ->with(['client', 'company', 'pdfFiles'])
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
            $paymentMethods = PaymentMethod::active()->orderBy('name')->get();
        } else {
            // Admin e user veem apenas da sua empresa
            $companyId = session('tenant_company_id');
            $clients = Client::where('company_id', $companyId)->orderBy('fantasy_name')->get();
            $companies = Company::where('id', $companyId)->orderBy('fantasy_name')->get();
            $products = Product::where('company_id', $companyId)->with(['category'])->orderBy('name')->get();
            $paymentMethods = PaymentMethod::forCompany($companyId)->active()->orderBy('name')->get();
        }
        
        return view('budgets.create', compact('clients', 'companies', 'products', 'paymentMethods'));
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
            'total_discount_perc' => 'nullable|numeric|min:0|max:100',
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

                // Buscar o nome do produto
                $product = Product::find($productData['product_id']);
                $productName = $product ? $product->name : '';

                BudgetItem::create([
                    'budget_id' => $budget->id,
                    'product_id' => $productData['product_id'],
                    'produto' => $productName,
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

            // Processar métodos de pagamento se fornecidos e se o usuário optou por incluí-los
            if ($request->include_payment_methods === 'yes' && $request->has('payment_methods') && is_array($request->payment_methods)) {
                foreach ($request->payment_methods as $index => $paymentData) {
                    // Verificar se os campos obrigatórios estão preenchidos
                    $hasPaymentMethodId = !empty($paymentData['payment_method_id']);
                    $hasAmount = !empty($paymentData['amount']);
                    $convertedAmount = $this->convertMoneyToFloat($paymentData['amount'] ?? '');
                    
                    if ($hasPaymentMethodId && $convertedAmount > 0) {
                        $budgetPayment = $budget->budgetPayments()->create([
                            'payment_method_id' => $paymentData['payment_method_id'],
                            'amount' => $convertedAmount,
                            'installments' => $paymentData['installments'] ?? 1,
                            'payment_moment' => $paymentData['payment_moment'] ?? 'approval',
                            'custom_date' => $paymentData['custom_date'] ?? null,
                            'days_after_pickup' => $paymentData['days_after_pickup'] ?? null,
                            'notes' => $paymentData['notes'] ?? null,
                            'order' => $paymentData['order'] ?? ($index + 1)
                        ]);
                        
                        // Criar parcelas automaticamente
                        $budgetPayment->createInstallments();
                    }
                }
            }
            
            DB::commit();
            
            // Gerar PDF automaticamente após criar o orçamento
            try {
                $this->generatePdfAutomatically($budget);
                return redirect()->route('budgets.show', $budget)->with('success', 'Orçamento criado com sucesso! PDF gerado automaticamente.');
            } catch (\Exception $pdfException) {
                \Illuminate\Support\Facades\Log::error('Error generating PDF after budget creation', ['error' => $pdfException->getMessage()]);
                return redirect()->route('budgets.show', $budget)->with('success', 'Orçamento criado com sucesso! Erro ao gerar PDF: ' . $pdfException->getMessage());
            }
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
        
        $budget->load(['client', 'company', 'items.product', 'pdfFiles', 'budgetPayments.paymentMethod', 'budgetPayments.paymentInstallments']);
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
            $paymentMethods = PaymentMethod::forCompany($budget->company_id)->orderBy('name')->get();
        } else {
            // Admin e user veem apenas da sua empresa
            $companyId = session('tenant_company_id');
            $clients = Client::where('company_id', $companyId)->orderBy('fantasy_name')->get();
            $companies = Company::where('id', $companyId)->orderBy('fantasy_name')->get();
            $products = Product::where('company_id', $companyId)->with(['category'])->orderBy('name')->get();
            $paymentMethods = PaymentMethod::forCompany($companyId)->orderBy('name')->get();
        }
        
        $budget->load(['items.product', 'budgetPayments.paymentMethod']);
        
        return view('budgets.edit', compact('budget', 'clients', 'companies', 'products', 'paymentMethods'));
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
            'total_discount_perc' => 'nullable|numeric|min:0|max:100',
            'observations' => 'nullable|string|max:1000',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'nullable|exists:products,id',
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
            // Verificar se o cliente foi alterado para gerenciar PDFs
            $clientChanged = $budget->client_id != $clientId;
            
            // Se o cliente mudou, excluir PDFs antigos
            if ($clientChanged) {
                $pdfFiles = \App\Models\PdfFile::where('budget_id', $budget->id)->get();
                foreach ($pdfFiles as $pdfFile) {
                    // Excluir arquivo físico
                    $filePath = public_path('pdfs/' . $pdfFile->filename);
                    if (file_exists($filePath)) {
                        unlink($filePath);
                    }
                }
                // Excluir registros da tabela pdf_files
                \App\Models\PdfFile::where('budget_id', $budget->id)->delete();
            }
            
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

            // Salvar nomes dos produtos excluídos antes de deletar
            $excludedProducts = [];
            foreach ($budget->items as $item) {
                if (!$item->product_id && $item->produto) {
                    $excludedProducts[$item->id] = $item->produto;
                }
            }

            // Remover itens antigos
            $budget->items()->delete();

            // Criar novos itens
            $totalAmount = 0;
            foreach ($request->items as $index => $itemData) {
                $itemTotal = $itemData['quantity'] * $itemData['unit_price'];
                $discountAmount = $itemTotal * (($itemData['discount_percentage'] ?? 0) / 100);
                $totalPrice = $itemTotal - $discountAmount;
                $totalAmount += $totalPrice;

                // Buscar o nome do produto ou manter o nome do produto excluído
                $productId = $itemData['product_id'] ?? null;
                if ($productId) {
                    $product = Product::find($productId);
                    $productName = $product ? $product->name : '';
                } else {
                    // Usar o nome do produto excluído salvo anteriormente
                    $productName = isset($itemData['produto_name']) ? $itemData['produto_name'] : 
                                  (count($excludedProducts) > 0 ? array_values($excludedProducts)[0] : '');
                }

                BudgetItem::create([
                    'budget_id' => $budget->id,
                    'product_id' => $productId,
                    'produto' => $productName,
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

            // Atualizar métodos de pagamento
            // Remover pagamentos antigos
            $budget->budgetPayments()->delete();
            
            // Processar novos métodos de pagamento se fornecidos e se o usuário optou por incluí-los
            if ($request->include_payment_methods === 'yes' && $request->has('payment_methods') && is_array($request->payment_methods)) {
                foreach ($request->payment_methods as $paymentData) {
                    if (!empty($paymentData['payment_method_id']) && !empty($paymentData['amount'])) {
                        $budgetPayment = $budget->budgetPayments()->create([
                            'payment_method_id' => $paymentData['payment_method_id'],
                            'amount' => $this->convertMoneyToFloat($paymentData['amount']),
                            'installments' => $paymentData['installments'] ?? 1,
                            'payment_moment' => $paymentData['payment_moment'] ?? 'approval',
                            'custom_date' => $paymentData['custom_date'] ?? null,
                            'days_after_pickup' => $paymentData['days_after_pickup'] ?? null,
                            'notes' => $paymentData['notes'] ?? null,
                            'order' => $paymentData['order'] ?? 1
                        ]);
                        
                        // Criar parcelas automaticamente
                        $budgetPayment->createInstallments();
                    }
                }
            }

            DB::commit();
            
            // Gerar PDF automaticamente após atualizar o orçamento
            try {
                $this->generatePdfAutomatically($budget);
                return redirect()->route('budgets.show', $budget)->with('success', 'Orçamento atualizado com sucesso! PDF gerado automaticamente.');
            } catch (\Exception $pdfException) {
                \Illuminate\Support\Facades\Log::error('Error generating PDF after budget update', ['error' => $pdfException->getMessage()]);
                return redirect()->route('budgets.show', $budget)->with('success', 'Orçamento atualizado com sucesso! Erro ao gerar PDF: ' . $pdfException->getMessage());
            }
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
            // Buscar todos os arquivos PDF relacionados ao orçamento na tabela pdf_files
            $pdfFiles = \App\Models\PdfFile::where('budget_id', $budget->id)->get();
            
            // Excluir os arquivos físicos do servidor
            foreach ($pdfFiles as $pdfFile) {
                $filePath = storage_path('app/public/pdfs/' . $pdfFile->filename);
                if (file_exists($filePath)) {
                    unlink($filePath);
                }
            }
            
            // Excluir registros da tabela pdf_files (será feito automaticamente pelo cascade)
            // mas vamos fazer manualmente para garantir
            \App\Models\PdfFile::where('budget_id', $budget->id)->delete();
            
            // Excluir o orçamento do banco de dados
            $budget->delete();
            
            $deletedFilesCount = $pdfFiles->count();
            $message = $deletedFilesCount > 0 
                ? "Orçamento excluído com sucesso! {$deletedFilesCount} arquivo(s) PDF também foram removidos."
                : 'Orçamento excluído com sucesso!';
            
            return redirect()->route('budgets.index')->with('success', $message);
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
        
        $budget->load(['client', 'items.product', 'budgetPayments.paymentMethod']);
        
        // Sistema de limpeza: comparar arquivos na pasta com registros na tabela
        $this->cleanupOrphanedPdfFiles();
        
        // Excluir PDFs antigos antes de gerar um novo
        $oldPdfFiles = \App\Models\PdfFile::where('budget_id', $budget->id)->get();
        foreach ($oldPdfFiles as $oldPdfFile) {
            // Excluir arquivo físico antigo
            $oldFilePath = storage_path('app/public/pdfs/' . $oldPdfFile->filename);
            if (file_exists($oldFilePath)) {
                unlink($oldFilePath);
            }
        }
        // Excluir registros antigos da tabela pdf_files
        \App\Models\PdfFile::where('budget_id', $budget->id)->delete();
        
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
        
        // Registrar na tabela pdf_files
        \App\Models\PdfFile::create([
            'budget_id' => $budget->id,
            'company_id' => $budget->company_id,
            'filename' => $filename
        ]);
        
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
    
    /**
     * Limpa arquivos PDF órfãos que existem na pasta mas não estão na tabela pdf_files
     */
    private function cleanupOrphanedPdfFiles()
    {
        try {
            $pdfDirectory = storage_path('app/public/pdfs/');
            
            // Verificar se o diretório existe
            if (!is_dir($pdfDirectory)) {
                return;
            }
            
            // Obter todos os arquivos PDF na pasta
            $filesInDirectory = [];
            $files = scandir($pdfDirectory);
            foreach ($files as $file) {
                if (pathinfo($file, PATHINFO_EXTENSION) === 'pdf') {
                    $filesInDirectory[] = $file;
                }
            }
            
            // Obter todos os nomes de arquivos registrados na tabela pdf_files
            $filesInDatabase = \App\Models\PdfFile::pluck('filename')->toArray();
            
            // Encontrar arquivos órfãos (existem na pasta mas não na tabela)
            $orphanedFiles = array_diff($filesInDirectory, $filesInDatabase);
            
            // Excluir arquivos órfãos
            $deletedCount = 0;
            foreach ($orphanedFiles as $orphanedFile) {
                $filePath = $pdfDirectory . $orphanedFile;
                if (file_exists($filePath)) {
                    unlink($filePath);
                    $deletedCount++;
                }
            }
            
            // Log para debug (opcional)
            if ($deletedCount > 0) {
                \Log::info("Sistema de limpeza PDF: {$deletedCount} arquivos órfãos removidos.");
            }
            
        } catch (\Exception $e) {
             // Em caso de erro, apenas registrar no log sem interromper o processo
             \Log::error('Erro no sistema de limpeza de PDFs: ' . $e->getMessage());
         }
     }
     
     /**
      * Atualiza o status do orçamento via AJAX
      */
     public function updateStatus(Request $request, Budget $budget)
    {
        $user = auth()->guard('web')->user();
         
         // Super admin pode alterar status de qualquer orçamento
         if ($user->role !== 'super_admin') {
             // Verificar se o orçamento pertence à empresa do usuário
             if ($budget->company_id !== session('tenant_company_id')) {
                 return response()->json(['success' => false, 'message' => 'Acesso negado'], 403);
             }
         }
         
         $request->validate([
             'status' => 'required|string|in:Pendente,Enviado,Em negociação,Aprovado,Expirado,Concluído',
             'redirect_to' => 'nullable|string|in:index,show,dashboard'
         ]);
         
         $budget->status = $request->status;
         $budget->save();
         
         // Determinar para onde redirecionar baseado no parâmetro redirect_to
         if ($request->redirect_to === 'show') {
             return redirect()->route('budgets.show', $budget)
                 ->with('success', 'Status atualizado com sucesso!');
         } elseif ($request->redirect_to === 'dashboard') {
             return redirect()->route('dashboard')
                 ->with('success', 'Status atualizado com sucesso!');
         }
         
         return redirect()->route('budgets.index')
             ->with('success', 'Status atualizado com sucesso!');
     }

    /**
     * Send budget PDF via WhatsApp.
     */
    public function sendWhatsApp(Budget $budget)
    {
        try {
            $user = auth()->guard('web')->user();
            
            // Super admin pode enviar qualquer orçamento
            if ($user->role !== 'super_admin') {
                // Verificar se o orçamento pertence à empresa do usuário
                if ($budget->company_id !== session('tenant_company_id')) {
                    abort(404);
                }
            }
            
            // Verificar se existe PDF para este orçamento
            $pdfFile = \App\Models\PdfFile::where('budget_id', $budget->id)->first();
            
            if (!$pdfFile) {
                return redirect()->back()->with('error', 'Nenhum PDF encontrado para este orçamento. Gere o PDF primeiro.');
            }
            
            // Verificar se o arquivo físico existe
            $filePath = storage_path('app/public/pdfs/' . $pdfFile->filename);
            if (!file_exists($filePath)) {
                return redirect()->back()->with('error', 'Arquivo PDF não encontrado no servidor.');
            }
            
            // Carregar relacionamentos necessários
            $budget->load(['client.contacts', 'company']);
            
            // Verificar se foi solicitado envio direto para o cliente
            $forceClient = request()->get('force_client', false);
            
            if ($forceClient) {
                // Forçar envio direto para o cliente
                if (empty($budget->client->phone)) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Cliente não possui telefone cadastrado.'
                    ], 400);
                }
                
                $whatsappUrl = $this->generateWhatsAppUrl($budget, $budget->client->phone);
                
                // Atualizar status do orçamento para 'Enviado' quando WhatsApp é gerado
                $budget->status = 'Enviado';
                $budget->save();
                
                \Illuminate\Support\Facades\Log::info('Status do orçamento atualizado para Enviado após envio por WhatsApp', ['budget_id' => $budget->id]);
                
                return response()->json([
                    'success' => true,
                    'has_contacts' => false,
                    'whatsapp_url' => $whatsappUrl
                ]);
            }
            
            // Verificar se o cliente tem contatos
            $contacts = $budget->client->contacts;
            
            if ($contacts->isEmpty()) {
                // Se não há contatos, verifica se o cliente tem telefone
                if (empty($budget->client->phone)) {
                    return redirect()->back()->with('error', 'Cliente não possui telefone ou contatos cadastrados.');
                }
                
                // Envia diretamente para o telefone do cliente
                $whatsappUrl = $this->generateWhatsAppUrl($budget, $budget->client->phone);
                
                // Atualizar status do orçamento para 'Enviado' quando WhatsApp é gerado
                $budget->status = 'Enviado';
                $budget->save();
                
                \Illuminate\Support\Facades\Log::info('Status do orçamento atualizado para Enviado após envio por WhatsApp', ['budget_id' => $budget->id]);
                
                // Se for requisição AJAX, retorna JSON
                if (request()->ajax()) {
                    return response()->json([
                        'success' => true,
                        'has_contacts' => false,
                        'whatsapp_url' => $whatsappUrl
                    ]);
                }
                
                return redirect($whatsappUrl);
            }
            
            // Se há contatos, retorna dados para a modal
            return response()->json([
                'success' => true,
                'has_contacts' => true,
                'client' => [
                    'id' => $budget->client->id,
                    'name' => $budget->client->fantasy_name,
                    'phone' => $budget->client->phone
                ],
                'contacts' => $contacts->map(function($contact) {
                    return [
                        'id' => $contact->id,
                        'name' => $contact->name,
                        'phone' => $contact->phone
                    ];
                })
            ]);
            
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Erro ao processar WhatsApp: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Erro ao processar solicitação.'], 500);
        }
    }

    public function sendWhatsAppToContact(Request $request, Budget $budget)
    {
        try {
            $user = auth()->guard('web')->user();
            
            // Super admin pode enviar qualquer orçamento
            if ($user->role !== 'super_admin') {
                // Verificar se o orçamento pertence à empresa do usuário
                if ($budget->company_id !== session('tenant_company_id')) {
                    abort(404);
                }
            }
            
            $request->validate([
                'contact_id' => 'required|exists:contacts,id'
            ]);
            
            $contact = \App\Models\Contact::findOrFail($request->contact_id);
            
            // Verifica se o contato pertence ao cliente do orçamento
            if ($contact->client_id !== $budget->client_id) {
                return response()->json(['success' => false, 'message' => 'Contato não pertence ao cliente do orçamento.'], 400);
            }
            
            $whatsappUrl = $this->generateWhatsAppUrl($budget, $contact->phone, $contact->name);
            
            // Atualizar status do orçamento para 'Enviado' quando WhatsApp é gerado
            $budget->status = 'Enviado';
            $budget->save();
            
            \Illuminate\Support\Facades\Log::info('Status do orçamento atualizado para Enviado após envio por WhatsApp para contato', ['budget_id' => $budget->id, 'contact_id' => $contact->id]);
            
            return response()->json([
                'success' => true,
                'whatsapp_url' => $whatsappUrl
            ]);
            
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Erro ao enviar WhatsApp para contato: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Erro ao processar solicitação.'], 500);
        }
    }

    private function sendDirectWhatsApp(Budget $budget, $phone)
    {
        $whatsappUrl = $this->generateWhatsAppUrl($budget, $phone);
        return redirect($whatsappUrl);
    }

    private function generateWhatsAppUrl(Budget $budget, $phone, $contactName = null)
    {
        // Verificar se existe PDF para este orçamento
        $pdfFile = \App\Models\PdfFile::where('budget_id', $budget->id)->first();
        
        if (!$pdfFile) {
            throw new \Exception('Nenhum PDF encontrado para este orçamento.');
        }
        
        // Gerar URL do PDF com HTTPS
        $pdfUrl = secure_url('storage/pdfs/' . $pdfFile->filename);
        
        // Usar nome do contato se fornecido, senão usar nome fantasia do cliente
        $recipientName = $contactName ?: $budget->client->fantasy_name;
        
        // Preparar mensagem
        $message = "Olá, {$recipientName}!\n\n";
        $message .= "Seu orçamento está pronto! Clique no link abaixo para baixar.\n\n";
        $message .= "{$pdfUrl}\n\n";
        $message .= "Qualquer dúvida, estamos à disposição.\n\n";
        $message .= "{$budget->company->fantasy_name}";
        
        return $this->sendWhatsAppMessage($phone, $message);
    }

    /**
     * Send WhatsApp message using wa.me URL.
     */
    private function sendWhatsAppMessage($phone, $message)
    {
        // Limpar e formatar o número de telefone
        $phone = preg_replace('/[^0-9]/', '', $phone);
        
        // Se o número não começar com código do país, adicionar +55 (Brasil)
        if (!str_starts_with($phone, '55')) {
            $phone = '55' . $phone;
        }
        
        // Codificar a mensagem para URL
        $encodedMessage = urlencode($message);
        
        // Gerar URL do WhatsApp
        $whatsappUrl = "https://wa.me/{$phone}/?text={$encodedMessage}";
        
        // Retornar a URL para redirecionamento
        return $whatsappUrl;
    }

    /**
     * Send email with budget PDF
     */
    public function sendEmail(Budget $budget)
    {
        try {
            $user = auth()->guard('web')->user();
            
            // Super admin pode enviar qualquer orçamento
            if ($user->role !== 'super_admin') {
                // Verificar se o orçamento pertence à empresa do usuário
                if ($budget->company_id !== session('tenant_company_id')) {
                    abort(404);
                }
            }
            
            // Verificar se existe PDF para este orçamento
            $pdfFile = \App\Models\PdfFile::where('budget_id', $budget->id)->first();
            
            if (!$pdfFile) {
                return response()->json([
                    'success' => false,
                    'message' => 'Nenhum PDF encontrado para este orçamento. Gere o PDF primeiro.'
                ], 400);
            }
            
            // Verificar se o arquivo físico existe
            $filePath = storage_path('app/public/pdfs/' . $pdfFile->filename);
            if (!file_exists($filePath)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Arquivo PDF não encontrado no servidor.'
                ], 400);
            }
            
            // Carregar relacionamentos necessários
            $budget->load(['client.contacts', 'company']);
            
            // Verificar se foi solicitado envio direto para o cliente
            $forceClient = request()->get('force_client', false);
            
            if ($forceClient) {
                // Forçar envio direto para o cliente
                // Primeiro verifica se o cliente tem email direto
                if (!empty($budget->client->email)) {
                    $templateId = request()->get('template_id');
                    $result = $this->sendEmailToRecipient($budget, $budget->client->email, $budget->client->fantasy_name, $templateId);
                    
                    return response()->json([
                        'success' => $result['success'],
                        'message' => $result['message']
                    ]);
                }
                
                // Se não tem email direto, verifica se tem contatos com email
                $contacts = $budget->client->contacts->filter(function($contact) {
                    return !empty($contact->email);
                });
                
                if ($contacts->isEmpty()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Cliente não possui email ou contatos com email cadastrados.'
                    ], 400);
                }
                
                // Usa o primeiro contato com email
                $firstContact = $contacts->first();
                $templateId = request()->get('template_id');
                $result = $this->sendEmailToRecipient($budget, $firstContact->email, $firstContact->name, $templateId);
                
                return response()->json([
                    'success' => $result['success'],
                    'message' => $result['message']
                ]);
            }
            
            // Verificar se o cliente tem contatos com email
            $contacts = $budget->client->contacts->filter(function($contact) {
                return !empty($contact->email);
            });
            
            // Verificar se o cliente ou contatos têm email
            $hasClientEmail = !empty($budget->client->email);
            $hasContactEmails = !$contacts->isEmpty();
            
            if (!$hasClientEmail && !$hasContactEmails) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cliente não possui email ou contatos com email cadastrados.'
                ], 400);
            }
            
            // Buscar templates de email ativos da empresa
            $user = auth()->guard('web')->user();
            if ($user->role === 'super_admin') {
                // Super admin pode ver todos os templates
                $emailTemplates = \App\Models\EmailTemplate::where('is_active', true)->get();
            } else {
                // Para admin e user, buscar apenas templates da sua empresa
                $emailTemplates = \App\Models\EmailTemplate::where('is_active', true)
                    ->where('company_id', $user->company_id)
                    ->get();
            }
            
            // Sempre retorna dados para a modal, permitindo escolha do template
            return response()->json([
                'success' => true,
                'has_contacts' => $hasContactEmails || $hasClientEmail,
                'client' => [
                    'id' => $budget->client->id,
                    'name' => $budget->client->fantasy_name,
                    'email' => $budget->client->email
                ],
                'contacts' => $contacts->map(function($contact) {
                    return [
                        'id' => $contact->id,
                        'name' => $contact->name,
                        'email' => $contact->email
                    ];
                }),
                'email_templates' => $emailTemplates->map(function($template) {
                    return [
                        'id' => $template->id,
                        'name' => $template->name,
                        'subject' => $template->subject
                    ];
                })
            ]);
            
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Erro ao processar envio de email: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Erro ao processar solicitação.'], 500);
        }
    }

    /**
     * Send email to specific contact
     */
    public function sendEmailToContact(Request $request, Budget $budget)
    {
        try {
            $user = auth()->guard('web')->user();
            
            // Super admin pode enviar qualquer orçamento
            if ($user->role !== 'super_admin') {
                // Verificar se o orçamento pertence à empresa do usuário
                if ($budget->company_id !== session('tenant_company_id')) {
                    abort(404);
                }
            }
            
            $request->validate([
                'contact_id' => 'required|exists:contacts,id'
            ]);
            
            $contact = \App\Models\Contact::findOrFail($request->contact_id);
            
            // Verifica se o contato pertence ao cliente do orçamento
            if ($contact->client_id !== $budget->client_id) {
                return response()->json(['success' => false, 'message' => 'Contato não pertence ao cliente do orçamento.'], 400);
            }
            
            if (empty($contact->email)) {
                return response()->json(['success' => false, 'message' => 'Contato não possui email cadastrado.'], 400);
            }
            
            $templateId = $request->get('template_id');
            $result = $this->sendEmailToRecipient($budget, $contact->email, $contact->name, $templateId);
            
            return response()->json([
                'success' => $result['success'],
                'message' => $result['message']
            ]);
            
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Erro ao enviar email para contato: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Erro ao processar solicitação.'], 500);
        }
    }

    /**
     * Send email to recipient using Google API
     */
    private function sendEmailToRecipient(Budget $budget, $recipientEmail, $recipientName, $templateId = null)
    {
        try {
            // Verificar se existe PDF para este orçamento
            $pdfFile = \App\Models\PdfFile::where('budget_id', $budget->id)->first();
            
            if (!$pdfFile) {
                return ['success' => false, 'message' => 'Nenhum PDF encontrado para este orçamento.'];
            }
            
            // Verificar se o arquivo físico existe
            $filePath = storage_path('app/public/pdfs/' . $pdfFile->filename);
            if (!file_exists($filePath)) {
                return ['success' => false, 'message' => 'Arquivo PDF não encontrado no servidor.'];
            }
            
            // Carregar dados da empresa
            $budget->load('company');
            
            // Usar o GoogleEmailService para enviar o email
            $googleEmailService = new \App\Services\GoogleEmailService($budget->company_id);
            
            if (!$googleEmailService->isAuthenticated()) {
                return [
                    'success' => false,
                    'message' => 'Integração com Google não configurada. Configure primeiro nas configurações da empresa.',
                    'auth_required' => true
                ];
            }
            
            // Preparar dados do email
            $subject = "Orçamento #{$budget->number} - {$budget->company->fantasy_name}";
            
            // Preparar dados para o template HTML
            $budgetData = [
                'recipientName' => $recipientName,
                'budgetNumber' => $budget->number,
                'budgetValue' => number_format($budget->final_amount, 2, ',', '.'),
                'budgetDate' => $budget->created_at->format('d/m/Y'),
                'budgetValidity' => $budget->created_at->addDays(30)->format('d/m/Y'), // 30 dias de validade
                'budgetStatus' => 'Aguardando Aprovação',
                'companyName' => $budget->company->fantasy_name ?? 'Empresa',
                'companyPhone' => $budget->company->phone ?? '(00) 0000-0000',
                'companyEmail' => $budget->company->email ?? 'contato@empresa.com',
                'companyAddress' => $budget->company->address ?? 'Endereço não informado',
                'companyCity' => $budget->company->city ?? 'Cidade',
                'companyState' => $budget->company->state ?? 'Estado'
            ];
            
            $result = $googleEmailService->sendBudgetEmail(
                $recipientEmail,
                $subject,
                $budgetData,
                $filePath,
                $templateId
            );
            
            // Se o email foi enviado com sucesso, atualizar status do orçamento para 'Enviado'
            if ($result['success']) {
                $budget->status = 'Enviado';
                $budget->save();
                
                \Illuminate\Support\Facades\Log::info('Status do orçamento atualizado para Enviado após envio de email', ['budget_id' => $budget->id]);
            }
            
            return $result;
            
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Erro ao enviar email: ' . $e->getMessage());
            return ['success' => false, 'message' => 'Erro ao enviar email: ' . $e->getMessage()];
        }
    }
    
    /**
     * Gera PDF automaticamente após criar ou atualizar um orçamento
     * Versão simplificada do generatePdf que não retorna resposta HTTP
     */
    private function generatePdfAutomatically(Budget $budget)
    {
        $budget->load(['client', 'items.product', 'budgetPayments.paymentMethod']);
        
        // Limpar arquivos PDF órfãos antes de gerar um novo
        $this->cleanupOrphanedPdfFiles();
        
        // Excluir PDFs antigos antes de gerar um novo
        $oldPdfFiles = \App\Models\PdfFile::where('budget_id', $budget->id)->get();
        foreach ($oldPdfFiles as $oldPdfFile) {
            // Excluir arquivo físico antigo
            $oldFilePath = storage_path('app/public/pdfs/' . $oldPdfFile->filename);
            if (file_exists($oldFilePath)) {
                unlink($oldFilePath);
            }
        }
        // Excluir registros antigos da tabela pdf_files
        \App\Models\PdfFile::where('budget_id', $budget->id)->delete();
        
        $pdf = Pdf::loadView('pdf.budget', compact('budget'));
        $pdf->setPaper('A4', 'portrait');

        if(empty($budget->client->corporate_name)){
             $filename = Str::slug($budget->client->fantasy_name) . '-' . 'orcamento-' . str_replace('/', '-', $budget->number) . '.pdf';
        } else {
            $filename = Str::slug($budget->client->corporate_name) . '-' . 'orcamento-' . str_replace('/', '-', $budget->number) . '.pdf';
        }
        
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
        
        // Registrar na tabela pdf_files
        \App\Models\PdfFile::create([
            'budget_id' => $budget->id,
            'company_id' => $budget->company_id,
            'filename' => $filename
        ]);
        
        \Illuminate\Support\Facades\Log::info('PDF generated automatically', ['budget_id' => $budget->id, 'filename' => $filename]);
    }
}