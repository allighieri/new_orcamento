<?php

require_once 'vendor/autoload.php';

// Configurar Laravel
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Events\PaymentConfirmed;
use App\Models\Payment;
use Illuminate\Support\Facades\Log;

echo "=== TESTE DETALHADO DE EVENTOS DE PAGAMENTO ===\n\n";

// Buscar pagamentos com status específicos
$receivedPayments = Payment::whereNotNull('plan_id')
    ->where('status', 'received')
    ->orderBy('created_at', 'desc')
    ->limit(3)
    ->get();

$confirmedPayments = Payment::whereNotNull('plan_id')
    ->where('status', 'confirmed')
    ->orderBy('created_at', 'desc')
    ->limit(3)
    ->get();

echo "Pagamentos RECEIVED encontrados: " . $receivedPayments->count() . "\n";
echo "Pagamentos CONFIRMED encontrados: " . $confirmedPayments->count() . "\n\n";

// Testar múltiplas vezes para ver se há padrão
for ($round = 1; $round <= 3; $round++) {
    echo "=== RODADA {$round} ===\n\n";
    
    // Testar pagamentos RECEIVED
    echo "--- TESTANDO PAGAMENTOS RECEIVED ---\n";
    foreach ($receivedPayments as $payment) {
        $planType = null;
        if ($payment->plan_id) {
            $plan = \App\Models\Plan::find($payment->plan_id);
            $planType = $plan ? $plan->name : null;
        }
        
        $startTime = microtime(true);
        
        try {
            event(new PaymentConfirmed(
                $payment->id,
                $payment->status,
                $payment->company_id,
                $planType,
                $payment->amount
            ));
            
            $endTime = microtime(true);
            $executionTime = ($endTime - $startTime) * 1000;
            
            echo "Payment ID {$payment->id} (RECEIVED): " . number_format($executionTime, 2) . "ms\n";
            
        } catch (Exception $e) {
            echo "ERRO Payment ID {$payment->id}: " . $e->getMessage() . "\n";
        }
    }
    
    echo "\n--- TESTANDO PAGAMENTOS CONFIRMED ---\n";
    foreach ($confirmedPayments as $payment) {
        $planType = null;
        if ($payment->plan_id) {
            $plan = \App\Models\Plan::find($payment->plan_id);
            $planType = $plan ? $plan->name : null;
        }
        
        $startTime = microtime(true);
        
        try {
            event(new PaymentConfirmed(
                $payment->id,
                $payment->status,
                $payment->company_id,
                $planType,
                $payment->amount
            ));
            
            $endTime = microtime(true);
            $executionTime = ($endTime - $startTime) * 1000;
            
            echo "Payment ID {$payment->id} (CONFIRMED): " . number_format($executionTime, 2) . "ms\n";
            
        } catch (Exception $e) {
            echo "ERRO Payment ID {$payment->id}: " . $e->getMessage() . "\n";
        }
    }
    
    echo "\n" . str_repeat('-', 60) . "\n\n";
    
    // Pequena pausa entre rodadas
    if ($round < 3) {
        sleep(1);
    }
}

echo "=== TESTE CONCLUÍDO ===\n";