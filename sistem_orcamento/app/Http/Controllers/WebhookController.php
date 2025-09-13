<?php

namespace App\Http\Controllers;

use App\Jobs\ProcessAsaasWebhook;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class WebhookController extends Controller
{
    /**
     * Manipular webhook do Asaas com resposta rápida e processamento assíncrono
     */
    public function handleAsaasWebhook(Request $request)
    {
        try {
            // Capturar dados brutos da requisição
            $rawData = $request->getContent();
            
            Log::info('Webhook Asaas recebido - resposta rápida', [
                'headers' => $request->headers->all(),
                'content_length' => strlen($rawData),
                'method' => $request->method(),
                'url' => $request->fullUrl(),
                'timestamp' => now()->toISOString()
            ]);

            // Validação do token de segurança do webhook
            $webhookToken = $request->header('Asaas-Access-Token') ?? $request->header('X-Asaas-Signature');
            $expectedToken = env('ASAAS_WEBHOOK_TOKEN');
            
            if ($expectedToken && $webhookToken !== $expectedToken) {
                Log::warning('Token de webhook inválido', [
                    'received_token' => $webhookToken ? substr($webhookToken, 0, 10) . '...' : 'null',
                    'expected_token_exists' => !empty($expectedToken),
                    'headers' => $request->headers->keys()
                ]);
                return response()->json(['status' => 'Unauthorized'], 401);
            }

            // Tentar decodificar o JSON
            $payload = json_decode($rawData, true);
            
            if (json_last_error() !== JSON_ERROR_NONE) {
                Log::error('Erro ao decodificar JSON do webhook', [
                    'json_error' => json_last_error_msg(),
                    'raw_data_preview' => substr($rawData, 0, 200)
                ]);
                return response()->json(['error' => 'Invalid JSON'], 400);
            }

            // Validação básica do payload
            if (!isset($payload['event'])) {
                Log::warning('Webhook inválido - evento ausente', [
                    'payload_keys' => array_keys($payload)
                ]);
                return response()->json(['error' => 'Invalid payload'], 400);
            }

            $event = $payload['event'];
            
            // Validar se é um evento suportado
            $isPaymentEvent = str_starts_with($event, 'PAYMENT_') && isset($payload['payment']);
            $isSubscriptionEvent = str_starts_with($event, 'SUBSCRIPTION_') && isset($payload['subscription']);
            
            if (!$isPaymentEvent && !$isSubscriptionEvent) {
                Log::warning('Webhook com evento não suportado ou dados ausentes', [
                    'event' => $event,
                    'has_payment' => isset($payload['payment']),
                    'has_subscription' => isset($payload['subscription'])
                ]);
                return response()->json(['error' => 'Unsupported event or missing data'], 400);
            }

            // Despachar job para processamento assíncrono
            ProcessAsaasWebhook::dispatch($payload);
            
            Log::info('Webhook despachado para fila com sucesso', [
                'event' => $payload['event'],
                'payment_id' => $payload['payment']['id'] ?? null,
                'subscription_id' => $payload['subscription']['id'] ?? null
            ]);

            // Responder imediatamente com 200 OK para a Asaas
            return response()->json(['status' => 'OK'], 200);

        } catch (\Exception $e) {
            Log::error('Erro crítico no webhook - resposta rápida', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            // Mesmo com erro, retornar 200 para evitar reenvios desnecessários
            // O erro será tratado no job
            return response()->json(['status' => 'Error logged'], 200);
        }
    }

}
