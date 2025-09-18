<?php

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

echo "=== VERIFICANDO ESTRUTURA DA TABELA SUBSCRIPTIONS ===\n";

// Verificar se a tabela existe
if (Schema::hasTable('subscriptions')) {
    echo "Tabela 'subscriptions' existe.\n\n";
    
    // Obter estrutura da tabela
    $columns = DB::select('DESCRIBE subscriptions');
    
    echo "Colunas da tabela subscriptions:\n";
    foreach ($columns as $column) {
        echo "- {$column->Field}: {$column->Type} (Null: {$column->Null}, Default: {$column->Default})\n";
        
        if ($column->Field === 'billing_cycle') {
            echo "  >>> COLUNA BILLING_CYCLE ENCONTRADA <<<\n";
            echo "  Tipo: {$column->Type}\n";
            echo "  Permite NULL: {$column->Null}\n";
            echo "  Valor padrão: {$column->Default}\n";
        }
    }
    
    echo "\n=== VERIFICANDO VALORES EXISTENTES ===\n";
    $existingValues = DB::table('subscriptions')
        ->select('billing_cycle')
        ->distinct()
        ->get();
    
    echo "Valores únicos na coluna billing_cycle:\n";
    foreach ($existingValues as $value) {
        echo "- '{$value->billing_cycle}'\n";
    }
    
} else {
    echo "Tabela 'subscriptions' não existe.\n";
}

echo "\n=== TESTANDO INSERÇÃO ===\n";
try {
    // Testar inserção com 'annual'
    echo "Testando inserção com valor 'annual'...\n";
    DB::beginTransaction();
    
    $testId = DB::table('subscriptions')->insertGetId([
        'company_id' => 999,
        'plan_id' => 1,
        'status' => 'active',
        'billing_cycle' => 'yearly',
        'starts_at' => now(),
        'ends_at' => now()->addYear(),
        'grace_period_ends_at' => now()->addYear()->addDays(3),
        'start_date' => now(),
        'end_date' => now()->addYear(),
        'next_billing_date' => now()->addYear(),
        'amount_paid' => 300.00,
        'auto_renew' => false,
        'created_at' => now(),
        'updated_at' => now()
    ]);
    
    echo "Inserção bem-sucedida com ID: {$testId}\n";
    
    // Remover o registro de teste
    DB::table('subscriptions')->where('id', $testId)->delete();
    DB::rollback();
    
} catch (Exception $e) {
    DB::rollback();
    echo "ERRO na inserção: {$e->getMessage()}\n";
}

echo "\n=== FIM DA VERIFICAÇÃO ===\n";