<?php

require_once __DIR__ . '/vendor/autoload.php';

// Carregar configurações do Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Events\PaymentConfirmed;
use Illuminate\Support\Facades\Log;

echo "=== TESTE DIRETO DE BROADCAST ===\n\n";

try {
    echo "📤 Disparando evento PaymentConfirmed diretamente...\n";
    
    $event = new PaymentConfirmed(
        paymentId: 999,
        status: 'received',
        companyId: 1,
        planType: 'bronze',
        amount: 29.90
    );
    
    // Disparar o evento
    event($event);
    
    echo "✅ Evento disparado com sucesso!\n";
    echo "   - Payment ID: 999\n";
    echo "   - Status: received\n";
    echo "   - Company ID: 1\n";
    echo "   - Plan Type: bronze\n";
    echo "   - Amount: 29.90\n\n";
    
    echo "🎯 Verifique a página test-websocket-pix.html para ver se o evento foi recebido!\n";
    
} catch (Exception $e) {
    echo "❌ Erro ao disparar evento: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
}

echo "\n=== FIM DO TESTE ===\n";