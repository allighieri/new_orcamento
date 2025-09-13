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

echo "Empresa encontrada: {$company->fantasy_name} (ID: {$company->id})\n";
echo "Email: {$company->email}\n";
echo "Documento: {$company->document_number}\n\n";

// Verificar se a empresa tem plano ativo
$activeSubscription = $company->activeSubscription();
if (!$activeSubscription) {
    echo "Erro: Empresa não tem plano ativo!\n";
    
    // Verificar se há subscriptions
    $subscriptions = $company->subscriptions()->get();
    echo "Subscriptions encontradas: " . $subscriptions->count() . "\n";
    
    foreach ($subscriptions as $sub) {
        echo "- ID: {$sub->id}, Status: {$sub->status}, Plan ID: {$sub->plan_id}\n";
    }
    
    exit(1);
}

echo "Plano ativo encontrado: {$activeSubscription->plan->name}\n";
echo "Preço mensal: R$ {$activeSubscription->plan->monthly_price}\n";
echo "Limite de orçamentos: {$activeSubscription->plan->budget_limit}\n\n";

// Simular criação de pagamento
echo "=== SIMULANDO CRIAÇÃO DE PAGAMENTO ===\n";

$quantity = 10;
$totalAmount = $activeSubscription->plan->monthly_price;

echo "Quantidade de orçamentos extras: {$quantity}\n";
echo "Valor total: R$ {$totalAmount}\n\n";

// Verificar se AsaasService funciona
try {
    $asaasService = new App\Services\AsaasService();
    echo "AsaasService instanciado com sucesso\n";
    
    // Tentar buscar cliente
    $customers = $asaasService->findCustomerByCpfCnpj($company->document_number);
    echo "Clientes encontrados no Asaas: " . count($customers) . "\n";
    
    if (!empty($customers)) {
        $customer = $customers[0];
        echo "Cliente Asaas ID: {$customer['id']}\n";
    }
    
} catch (Exception $e) {
    echo "Erro no AsaasService: " . $e->getMessage() . "\n";
}

echo "\n=== TESTE CONCLUÍDO ===\n";