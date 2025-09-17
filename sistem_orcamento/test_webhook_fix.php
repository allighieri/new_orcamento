<?php

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Payment;
use App\Models\Subscription;
use App\Events\PaymentConfirmed;

echo "=== VERIFICANDO PAGAMENTOS RECENTES ===\n";

// Buscar pagamentos recentes
$payments = Payment::orderBy('id', 'desc')->take(5)->get();

foreach ($payments as $payment) {
    echo "ID: {$payment->id} | Asaas: {$payment->asaas_payment_id} | Status: {$payment->status} | Valor: {$payment->amount} | Ciclo: {$payment->billing_cycle} | Tipo: {$payment->billing_type} | Criado: {$payment->created_at}\n";
}

echo "\n=== BUSCANDO PAGAMENTO RECEIVED ===\n";

// Buscar o pagamento com status 'received' mais recente (pode ser o pagamento via cartão)
$receivedPayment = Payment::where('status', 'received')
    ->orderBy('id', 'desc')
    ->first();

if ($receivedPayment) {
    echo "Pagamento RECEIVED encontrado: ID {$receivedPayment->id}\n";
    echo "Asaas ID: {$receivedPayment->asaas_payment_id}\n";
    echo "Status: {$receivedPayment->status}\n";
    echo "Valor: {$receivedPayment->amount}\n";
    echo "Ciclo: {$receivedPayment->billing_cycle}\n";
    echo "Tipo: {$receivedPayment->billing_type}\n";
    echo "User ID: {$receivedPayment->user_id}\n";
    
    // Verificar se já tem assinatura
    $subscription = Subscription::where('payment_id', $receivedPayment->id)->first();
    if ($subscription) {
        echo "Assinatura já existe: ID {$subscription->id}, Status: {$subscription->status}\n";
    } else {
        echo "Nenhuma assinatura encontrada para este pagamento.\n";
        
        echo "\n=== TESTANDO EVENTO PaymentConfirmed ===\n";
        
        try {
            // Disparar o evento PaymentConfirmed com os parâmetros corretos
            event(new PaymentConfirmed(
                $receivedPayment->id,
                'confirmed',
                $receivedPayment->user_id,
                $receivedPayment->billing_cycle,
                $receivedPayment->amount
            ));
            echo "Evento PaymentConfirmed disparado com sucesso!\n";
            
            // Verificar se a assinatura foi criada após o evento
            sleep(2); // Aguardar um pouco mais
            $newSubscription = Subscription::where('payment_id', $receivedPayment->id)->first();
            if ($newSubscription) {
                echo "Assinatura criada após evento: ID {$newSubscription->id}, Status: {$newSubscription->status}\n";
            } else {
                echo "Assinatura ainda não foi criada.\n";
            }
            
        } catch (Exception $e) {
            echo "Erro ao disparar evento: " . $e->getMessage() . "\n";
        }
    }
} else {
    echo "Nenhum pagamento com status 'received' encontrado.\n";
}

echo "\n=== VERIFICANDO TODOS OS PAGAMENTOS CONFIRMED ===\n";

$confirmedPayments = Payment::where('status', 'confirmed')
    ->orderBy('id', 'desc')
    ->take(3)
    ->get();

foreach ($confirmedPayments as $payment) {
    echo "ID: {$payment->id} | Status: {$payment->status} | Valor: {$payment->amount} | Tipo: {$payment->billing_type}\n";
    
    $subscription = Subscription::where('payment_id', $payment->id)->first();
    if ($subscription) {
        echo "  -> Tem assinatura: ID {$subscription->id}, Status: {$subscription->status}\n";
    } else {
        echo "  -> SEM assinatura\n";
    }
}

echo "\n=== TESTE CONCLUÍDO ===\n";