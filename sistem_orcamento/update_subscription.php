<?php

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Subscription;

echo "Atualizando assinatura...\n";

$subscription = Subscription::find(13);
if ($subscription) {
    $subscription->asaas_subscription_id = 'sub_i16k0c2drglhqtyj';
    $subscription->save();
    echo "✅ Assinatura ID 13 atualizada com asaas_subscription_id: sub_i16k0c2drglhqtyj\n";
} else {
    echo "❌ Assinatura ID 13 não encontrada\n";
}

echo "Verificando resultado...\n";
$updated = Subscription::find(13);
echo "Asaas Subscription ID: {$updated->asaas_subscription_id}\n";