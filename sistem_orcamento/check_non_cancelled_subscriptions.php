<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

// Verificar assinaturas não canceladas da empresa ID 1
$subscriptions = \App\Models\Subscription::where('company_id', 1)
    ->where('status', '!=', 'cancelled')
    ->get();

echo "Assinaturas não canceladas: " . $subscriptions->count() . "\n\n";

foreach ($subscriptions as $sub) {
    echo "ID: {$sub->id}\n";
    echo "Status: {$sub->status}\n";
    echo "Plan ID: {$sub->plan_id}\n";
    echo "Ends At: " . ($sub->ends_at ?? 'null') . "\n";
    echo "Start Date: " . ($sub->start_date ?? 'null') . "\n";
    echo "End Date: " . ($sub->end_date ?? 'null') . "\n";
    echo "Created At: {$sub->created_at}\n";
    echo "---\n";
}

// Verificar especificamente o método activeSubscription
$company = \App\Models\Company::find(1);
$activeSubscription = $company->activeSubscription();

if ($activeSubscription) {
    echo "\nMétodo activeSubscription() retornou:\n";
    echo "ID: {$activeSubscription->id}\n";
    echo "Status: {$activeSubscription->status}\n";
    echo "Plan ID: {$activeSubscription->plan_id}\n";
    echo "Ends At: " . ($activeSubscription->ends_at ?? 'null') . "\n";
    echo "Now: " . now() . "\n";
    echo "Ends At >= Now: " . ($activeSubscription->ends_at >= now() ? 'true' : 'false') . "\n";
} else {
    echo "\nMétodo activeSubscription() retornou null\n";
}