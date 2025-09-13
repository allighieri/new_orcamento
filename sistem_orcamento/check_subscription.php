<?php

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "=== VERIFICANDO ASSINATURA ATIVA ===\n\n";

$subscription = DB::table('subscriptions')->where('status', 'active')->first();

if($subscription) {
    echo "ID: " . $subscription->id . "\n";
    echo "Company ID: " . $subscription->company_id . "\n";
    echo "Plan ID: " . $subscription->plan_id . "\n";
    echo "Billing Cycle: " . $subscription->billing_cycle . "\n";
    echo "Status: " . $subscription->status . "\n";
    echo "Amount Paid: " . $subscription->amount_paid . "\n";
    echo "Start Date: " . $subscription->start_date . "\n";
    echo "End Date: " . $subscription->end_date . "\n";
} else {
    echo "Nenhuma assinatura ativa encontrada\n";
}

echo "\n=== VERIFICANDO TODAS AS ASSINATURAS ===\n\n";

$allSubscriptions = DB::table('subscriptions')->orderBy('id', 'desc')->limit(5)->get();

foreach($allSubscriptions as $sub) {
    echo "ID: {$sub->id}, Company: {$sub->company_id}, Plan: {$sub->plan_id}, Cycle: {$sub->billing_cycle}, Status: {$sub->status}, Amount: {$sub->amount_paid}\n";
}