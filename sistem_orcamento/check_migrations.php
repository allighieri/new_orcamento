<?php
require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

try {
    $migrations = \Illuminate\Support\Facades\DB::table('migrations')->get();
    echo "Total migrations: " . count($migrations) . "\n";
    foreach($migrations as $migration) {
        echo $migration->migration . "\n";
    }
} catch(Exception $e) {
    echo "Erro: " . $e->getMessage() . "\n";
}