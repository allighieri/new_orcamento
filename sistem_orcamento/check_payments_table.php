<?php

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "=== ESTRUTURA DA TABELA PAYMENTS ===\n\n";

$columns = DB::select('DESCRIBE payments');
foreach($columns as $col) {
    $nullable = $col->Null == 'YES' ? 'NULL' : 'NOT NULL';
    $default = $col->Default ?? 'NONE';
    echo "{$col->Field} - {$col->Type} - {$nullable} - Default: {$default}\n";
}

echo "\n=== VERIFICANDO PAGAMENTOS EXISTENTES ===\n\n";

$payments = DB::table('payments')->select('id', 'plan_id', 'type', 'status')->limit(5)->get();
foreach($payments as $payment) {
    echo "ID: {$payment->id}, Plan ID: {$payment->plan_id}, Type: {$payment->type}, Status: {$payment->status}\n";
}