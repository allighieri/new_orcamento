<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

// Verificar assinaturas da empresa ID 1
$company = \App\Models\Company::find(1);
if (!$company) {
    echo "Empresa ID 1 não encontrada\n";
    exit;
}

echo "Empresa: {$company->name}\n";
echo "ID: {$company->id}\n\n";

// Buscar todas as assinaturas da empresa
$allSubscriptions = \App\Models\Subscription::where('company_id', 1)->get();
echo "Total de assinaturas: " . $allSubscriptions->count() . "\n\n";

foreach ($allSubscriptions as $subscription) {
    echo "Assinatura ID: {$subscription->id}\n";
    echo "Plan ID: {$subscription->plan_id}\n";
    echo "Status: {$subscription->status}\n";
    echo "Billing Cycle: {$subscription->billing_cycle}\n";
    echo "Start Date: {$subscription->start_date}\n";
    echo "End Date: {$subscription->end_date}\n";
    echo "Created At: {$subscription->created_at}\n";
    echo "---\n";
}

// Verificar assinatura ativa usando o método da model
$activeSubscription = $company->activeSubscription();
if ($activeSubscription) {
    echo "\nAssinatura ativa encontrada:\n";
    echo "ID: {$activeSubscription->id}\n";
    echo "Plan ID: {$activeSubscription->plan_id}\n";
    echo "Status: {$activeSubscription->status}\n";
    echo "Billing Cycle: {$activeSubscription->billing_cycle}\n";
} else {
    echo "\nNenhuma assinatura ativa encontrada\n";
}