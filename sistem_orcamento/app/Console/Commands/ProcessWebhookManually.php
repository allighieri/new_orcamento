<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Http\Controllers\WebhookController;
use Illuminate\Http\Request;

class ProcessWebhookManually extends Command
{
    protected $signature = 'webhook:process-manually {payment_id}';
    protected $description = 'Processar webhook manualmente com dados do pagamento';

    public function handle()
    {
        $paymentId = $this->argument('payment_id');
        
        // Dados reais do webhook recebido
        $webhookData = [
            "id" => "evt_d26e303b238e509335ac9ba210e51b0f&10414257",
            "event" => "PAYMENT_RECEIVED",
            "dateCreated" => "2025-09-12 19:05:40",
            "payment" => [
                "object" => "payment",
                "id" => $paymentId,
                "dateCreated" => "2025-09-12",
                "customer" => "cus_000007012837",
                "checkoutSession" => null,
                "paymentLink" => null,
                "value" => 45,
                "netValue" => 44.01,
                "originalValue" => null,
                "interestValue" => null,
                "description" => "Assinatura Anual do plano Ouro - ",
                "billingType" => "PIX",
                "confirmedDate" => "2025-09-12",
                "pixTransaction" => "37799d62-b966-4893-889b-534657c700a4",
                "pixQrCodeId" => "2226a827-dc19-409c-af30-ac6862f64b35",
                "status" => "RECEIVED",
                "dueDate" => "2025-09-13",
                "originalDueDate" => "2025-09-13",
                "paymentDate" => "2025-09-12",
                "clientPaymentDate" => "2025-09-12",
                "installmentNumber" => null,
                "invoiceUrl" => "https://sandbox.asaas.com/i/0j0r8gb9eaf65vyv",
                "invoiceNumber" => "11304888",
                "externalReference" => null,
                "deleted" => false,
                "anticipated" => false,
                "anticipable" => false,
                "creditDate" => "2025-09-12",
                "estimatedCreditDate" => "2025-09-12",
                "transactionReceiptUrl" => "https://sandbox.asaas.com/comprovantes/h/UEFZTUVOVF9SRUNFSVZFRDpwYXlfMGowcjhnYjllYWY2NXZ5dg%3D%3D",
                "nossoNumero" => null,
                "bankSlipUrl" => null,
                "lastInvoiceViewedDate" => null,
                "lastBankSlipViewedDate" => null,
                "discount" => [
                    "value" => 0,
                    "limitDate" => null,
                    "dueDateLimitDays" => 0,
                    "type" => "FIXED"
                ],
                "fine" => [
                    "value" => 0,
                    "type" => "FIXED"
                ],
                "interest" => [
                    "value" => 0,
                    "type" => "PERCENTAGE"
                ],
                "postalService" => false,
                "escrow" => null,
                "refunds" => null
            ]
        ];
        
        $this->info('Processando webhook manualmente...');
        $this->info('Payment ID: ' . $paymentId);
        
        try {
            // Criar uma requisição fake
            $request = new Request();
            $request->merge($webhookData);
            
            // Instanciar o controller
            $webhookController = app(WebhookController::class);
            
            // Processar o webhook
            $response = $webhookController->handleAsaasWebhook($request);
            
            $this->info('Resposta do webhook:');
            $this->info($response->getContent());
            $this->info('Status Code: ' . $response->getStatusCode());
            
        } catch (\Exception $e) {
            $this->error('Erro ao processar webhook:');
            $this->error($e->getMessage());
            $this->error('Stack trace:');
            $this->error($e->getTraceAsString());
        }
        
        return 0;
    }
}