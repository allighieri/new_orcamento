<?php

require_once __DIR__ . '/vendor/autoload.php';

// Carregar configurações do Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Payment;

echo "=== VERIFICAÇÃO DE PAGAMENTOS PIX vs CARTÃO ===\n\n";

// Buscar pagamentos recentes
$payments = Payment::whereNotNull('asaas_payment_id')
    ->orderBy('id', 'desc')
    ->take(15)
    ->get(['id', 'asaas_payment_id', 'status', 'type', 'amount', 'created_at', 'confirmed_at']);

echo "📊 Últimos 15 pagamentos com asaas_payment_id:\n";
echo "ID\t| Asaas ID\t\t\t| Status\t\t| Type\t\t| Amount\t| Created\t\t| Confirmed\n";
echo str_repeat('-', 120) . "\n";

foreach ($payments as $payment) {
    $createdAt = $payment->created_at ? $payment->created_at->format('Y-m-d H:i') : 'N/A';
    $confirmedAt = $payment->confirmed_at ? $payment->confirmed_at->format('Y-m-d H:i') : 'N/A';
    
    echo sprintf(
        "%d\t| %s\t| %s\t\t| %s\t\t| %.2f\t| %s\t| %s\n",
        $payment->id,
        $payment->asaas_payment_id,
        $payment->status,
        $payment->type ?? 'null',
        $payment->amount,
        $createdAt,
        $confirmedAt
    );
}

echo "\n=== ESTATÍSTICAS ===\n";

// Contar por status
$statusCounts = Payment::whereNotNull('asaas_payment_id')
    ->selectRaw('status, COUNT(*) as count')
    ->groupBy('status')
    ->get();

echo "\n📈 Pagamentos por status:\n";
foreach ($statusCounts as $stat) {
    echo "- {$stat->status}: {$stat->count}\n";
}

// Contar por type
$typeCounts = Payment::whereNotNull('asaas_payment_id')
    ->selectRaw('type, COUNT(*) as count')
    ->groupBy('type')
    ->get();

echo "\n📈 Pagamentos por tipo:\n";
foreach ($typeCounts as $stat) {
    $type = $stat->type ?? 'null';
    echo "- {$type}: {$stat->count}\n";
}

echo "\n=== FIM DA VERIFICAÇÃO ===\n";