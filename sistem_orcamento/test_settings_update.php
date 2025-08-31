<?php

require 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\CompanySetting;
use Illuminate\Http\Request;
use App\Http\Controllers\SettingsController;

// Verificar estado atual
$settings = CompanySetting::first();
echo "Estado atual do border: " . $settings->border . PHP_EOL;

// Simular requisição com border desmarcado (sem enviar o campo)
$request1 = new Request();
$request1->merge([
    'budget_validity_days' => 30,
    'budget_delivery_days' => 30,
    // border não enviado (desmarcado)
]);

echo "Testando com border desmarcado (campo não enviado)..." . PHP_EOL;
echo "request->has('border'): " . ($request1->has('border') ? 'true' : 'false') . PHP_EOL;
echo "Valor que seria salvo: " . ($request1->has('border') ? 1 : 0) . PHP_EOL;

// Simular requisição com border marcado
$request2 = new Request();
$request2->merge([
    'budget_validity_days' => 30,
    'budget_delivery_days' => 30,
    'border' => '1'
]);

echo "\nTestando com border marcado (campo enviado)..." . PHP_EOL;
echo "request->has('border'): " . ($request2->has('border') ? 'true' : 'false') . PHP_EOL;
echo "Valor que seria salvo: " . ($request2->has('border') ? 1 : 0) . PHP_EOL;