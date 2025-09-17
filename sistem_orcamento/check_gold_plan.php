<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Plan;

$plan = Plan::where('name', 'Ouro')->first();

if ($plan) {
    echo "Plano Ouro encontrado:\n";
    echo "ID: {$plan->id}\n";
    echo "Nome: {$plan->name}\n";
    echo "Budget Limit: " . ($plan->budget_limit ?? 'NULL') . "\n";
    echo "isUnlimited(): " . ($plan->isUnlimited() ? 'true' : 'false') . "\n";
} else {
    echo "Plano Ouro não encontrado!\n";
}