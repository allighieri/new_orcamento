<?php

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "=== TESTE DE PAGAMENTO DE ORÇAMENTOS EXTRAS ===\n\n";

// Buscar a empresa
$company = App\Models\Company::first();
if (!$company) {
    echo "Erro: Nenhuma empresa encontrada!\n";
    exit(1);
}

echo "Empresa: {$company->fantasy_name}\n";

// Verificar plano ativo
$activeSubscription = $company->activeSubscription();
if (!$activeSubscription) {
    echo "Erro: Empresa não tem plano ativo!\n";
    exit(1);
}

echo "Plano ativo: {$activeSubscription->plan->name}\n";

// Simular criação de pagamento
$quantity = 5;
$totalAmount = $activeSubscription->plan->monthly_price;

echo "Quantidade: {$quantity}\n";
echo "Valor: R$ {$totalAmount}\n\n";

try {
    // Criar registro de pagamento
    $payment = App\Models\Payment::create([
        'company_id' => $company->id,
        'plan_id' => null, // NULL para orçamentos extras
        'amount' => $totalAmount,
        'billing_type' => 'PIX',
        'type' => 'extra_budgets',
        'status' => 'PENDING',
        'due_date' => now()->addDays(1)->format('Y-m-d'),
        'description' => "Compra de {$activeSubscription->plan->budget_limit} orçamentos extras limitados ao período do seu plano - {$company->fantasy_name}",
        'extra_budgets_quantity' => $quantity,
        'billing_cycle' => 'monthly'
    ]);
    
    echo "✅ Pagamento criado com sucesso!\n";
    echo "ID do pagamento: {$payment->id}\n";
    echo "Plan ID: " . ($payment->plan_id ?? 'NULL') . "\n";
    echo "Type: {$payment->type}\n";
    echo "Status: {$payment->status}\n";
    
    // Agora testar integração com Asaas
    echo "\n=== TESTANDO INTEGRAÇÃO COM ASAAS ===\n";
    
    $asaasService = new App\Services\AsaasService();
    
    // Buscar ou criar cliente
    $customers = $asaasService->findCustomerByCpfCnpj($company->document_number);
    
    if (empty($customers)) {
        $customerData = [
            'name' => $company->fantasy_name,
            'email' => $company->email,
            'phone' => $company->phone ?? '',
            'cpfCnpj' => $company->document_number
        ];
        $customer = $asaasService->createCustomer($customerData);
        echo "Cliente criado no Asaas: {$customer['id']}\n";
    } else {
        $customer = $customers[0];
        echo "Cliente encontrado no Asaas: {$customer['id']}\n";
    }
    
    // Criar cobrança PIX
    $paymentData = [
        'customer' => $customer['id'],
        'value' => $totalAmount,
        'dueDate' => now()->addDays(1)->format('Y-m-d'),
        'description' => $payment->description
    ];
    
    $asaasPayment = $asaasService->createPixCharge($paymentData);
    
    echo "✅ Cobrança PIX criada no Asaas: {$asaasPayment['id']}\n";
    echo "Status: {$asaasPayment['status']}\n";
    echo "Valor: R$ {$asaasPayment['value']}\n";
    
    // Atualizar pagamento local
    $payment->update([
        'asaas_payment_id' => $asaasPayment['id'],
        'asaas_customer_id' => $customer['id'],
        'asaas_invoice_url' => $asaasPayment['invoiceUrl'] ?? null,
        'due_date' => $asaasPayment['dueDate']
    ]);
    
    echo "✅ Pagamento local atualizado com dados do Asaas\n";
    
} catch (Exception $e) {
    echo "❌ Erro: " . $e->getMessage() . "\n";
    echo "Trace: " . $e->getTraceAsString() . "\n";
}

echo "\n=== TESTE CONCLUÍDO ===\n";