<?php

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "Usuários cadastrados: " . App\Models\User::count() . PHP_EOL;

App\Models\User::take(5)->get(['id', 'name', 'email'])->each(function($user) {
    echo "ID: {$user->id} - Nome: {$user->name} - Email: {$user->email}" . PHP_EOL;
});

echo "\nEmpresas cadastradas: " . App\Models\Company::count() . PHP_EOL;

App\Models\Company::take(3)->get(['id', 'fantasy_name', 'email'])->each(function($company) {
    echo "ID: {$company->id} - Nome: {$company->fantasy_name} - Email: {$company->email}" . PHP_EOL;
});