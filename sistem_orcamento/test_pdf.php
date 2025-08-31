<?php

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

try {
    // Buscar um orçamento
    $budget = \App\Models\Budget::with(['client', 'items.product', 'budgetPayments.paymentMethod', 'budgetPayments.paymentInstallments', 'bankAccounts.compe'])->first();
    
    if (!$budget) {
        echo "Nenhum orçamento encontrado\n";
        exit(1);
    }
    
    echo "Orçamento encontrado: {$budget->number}\n";
    echo "Cliente: {$budget->client->fantasy_name}\n";
    
    // Obter configurações da empresa
    $settings = \App\Models\CompanySetting::getForCompany($budget->company_id);
    
    echo "Configurações carregadas\n";
    
    // Tentar gerar o PDF
    $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pdf.budget', compact('budget', 'settings'));
    $pdf->setPaper('A4', 'portrait');
    
    $output = $pdf->output();
    $size = strlen($output);
    
    echo "PDF gerado com sucesso!\n";
    echo "Tamanho: {$size} bytes\n";
    
    if ($size < 1000) {
        echo "AVISO: PDF muito pequeno, pode estar vazio!\n";
        // Salvar para análise
        file_put_contents('debug_pdf.pdf', $output);
        echo "PDF salvo como debug_pdf.pdf para análise\n";
    }
    
} catch (Exception $e) {
    echo "Erro: " . $e->getMessage() . "\n";
    echo "Trace: " . $e->getTraceAsString() . "\n";
}