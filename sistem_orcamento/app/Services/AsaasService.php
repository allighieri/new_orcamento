<?php

namespace App\Services;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Support\Facades\Log;

class AsaasService
{
    private $client;
    private $apiKey;
    private $baseUrl;
    private $sandbox;

    public function __construct()
    {
        $this->apiKey = config('services.asaas.api_key');
        $this->sandbox = config('services.asaas.sandbox', true);
        $this->baseUrl = $this->sandbox 
            ? 'https://sandbox.asaas.com/api/v3/'
            : 'https://www.asaas.com/api/v3/';

        $this->client = new Client([
            'base_uri' => $this->baseUrl,
            'headers' => [
                'access_token' => $this->apiKey,
                'Content-Type' => 'application/json',
                'Accept' => 'application/json'
            ],
            'timeout' => 30, // Reduzido para 30 segundos para evitar travamentos
            'connect_timeout' => 10, // Timeout de conexão
            'verify' => false // Desabilita verificação SSL para sandbox
        ]);
    }

    /**
     * Criar cobrança PIX
     */
    public function createPixCharge($data)
    {
        try {
            $paymentData = array_merge($data, [
                'billingType' => 'PIX'
            ]);
            
            Log::info('Criando cobrança PIX dinâmica', [
                'paymentData' => $paymentData
            ]);
            
            $response = $this->client->post('payments', [
                'json' => $paymentData
            ]);

            $responseData = json_decode($response->getBody()->getContents(), true);
            
            Log::info('Cobrança PIX criada com sucesso', [
                'payment_id' => $responseData['id'] ?? null,
                'status' => $responseData['status'] ?? null,
                'billing_type' => $responseData['billingType'] ?? null,
                'status_code' => $response->getStatusCode(),
                'full_response' => $responseData
            ]);

            return $responseData;
        } catch (RequestException $e) {
            Log::error('Erro ao criar cobrança PIX no Asaas', [
                'error' => $e->getMessage(),
                'data' => $data,
                'response_body' => $e->hasResponse() ? $e->getResponse()->getBody()->getContents() : null
            ]);
            throw $e;
        }
    }

    /**
     * Criar cobrança Cartão de Crédito
     */
    public function createCreditCardCharge($data)
    {
        try {
            $response = $this->client->post('payments', [
                'json' => array_merge($data, [
                    'billingType' => 'CREDIT_CARD'
                ])
            ]);

            return json_decode($response->getBody()->getContents(), true);
        } catch (RequestException $e) {
            Log::error('Erro ao criar cobrança de cartão no Asaas', [
                'error' => $e->getMessage(),
                'data' => $data
            ]);
            throw $e;
        }
    }

    /**
     * Buscar status de pagamento
     */
    public function getPaymentStatus($paymentId)
    {
        try {
            $response = $this->client->get("payments/{$paymentId}");
            return json_decode($response->getBody()->getContents(), true);
        } catch (RequestException $e) {
            Log::error('Erro ao buscar status do pagamento no Asaas', [
                'error' => $e->getMessage(),
                'paymentId' => $paymentId
            ]);
            throw $e;
        }
    }

    /**
     * Buscar informações detalhadas da fatura/cobrança
     */
    public function getPaymentBillingInfo($paymentId)
    {
        try {
            $response = $this->client->get("payments/{$paymentId}/billingInfo");
            return json_decode($response->getBody()->getContents(), true);
        } catch (RequestException $e) {
            Log::error('Erro ao buscar informações de cobrança no Asaas', [
                'error' => $e->getMessage(),
                'paymentId' => $paymentId
            ]);
            throw $e;
        }
    }

    /**
     * Buscar dados do cliente
     */
    public function getCustomer($customerId)
    {
        try {
            $response = $this->client->get("customers/{$customerId}");
            return json_decode($response->getBody()->getContents(), true);
        } catch (RequestException $e) {
            Log::error('Erro ao buscar dados do cliente no Asaas', [
                'error' => $e->getMessage(),
                'customerId' => $customerId
            ]);
            throw $e;
        }
    }

    /**
     * Criar cliente no Asaas
     */
    public function createCustomer($data)
    {
        try {
            $response = $this->client->post('customers', [
                'json' => $data
            ]);

            return json_decode($response->getBody()->getContents(), true);
        } catch (RequestException $e) {
            Log::error('Erro ao criar cliente no Asaas', [
                'error' => $e->getMessage(),
                'data' => $data
            ]);
            throw $e;
        }
    }

    /**
     * Buscar cliente por CPF/CNPJ
     */
    public function findCustomerByCpfCnpj($cpfCnpj)
    {
        try {
            $response = $this->client->get('customers', [
                'query' => ['cpfCnpj' => $cpfCnpj]
            ]);

            $result = json_decode($response->getBody()->getContents(), true);
            return $result['data'] ?? [];
        } catch (RequestException $e) {
            Log::error('Erro ao buscar cliente no Asaas', [
                'error' => $e->getMessage(),
                'cpfCnpj' => $cpfCnpj
            ]);
            throw $e;
        }
    }

    /**
     * Validar webhook do Asaas
     */
    public function validateWebhook($payload, $signature)
    {
        // Implementar validação de assinatura do webhook se necessário
        return true;
    }

    /**
     * Gerar QR Code PIX dinâmico para cobrança
     */
    public function getPixQrCode($paymentId)
    {
        Log::info('Gerando QR Code PIX dinâmico', ['payment_id' => $paymentId]);
        
        // Primeiro, verificar o status do pagamento
        try {
            $paymentResponse = $this->client->get("payments/{$paymentId}");
            $paymentData = json_decode($paymentResponse->getBody()->getContents(), true);
            
            Log::info('Status do pagamento verificado', [
                'payment_id' => $paymentId,
                'status' => $paymentData['status'] ?? 'unknown',
                'billing_type' => $paymentData['billingType'] ?? 'unknown'
            ]);
            
            // Verificar se o pagamento está no status correto para gerar QR Code
            if (!isset($paymentData['status']) || $paymentData['status'] !== 'PENDING') {
                throw new \Exception("Pagamento não está no status PENDING. Status atual: " . ($paymentData['status'] ?? 'unknown'));
            }
            
            if (!isset($paymentData['billingType']) || $paymentData['billingType'] !== 'PIX') {
                throw new \Exception("Pagamento não é do tipo PIX. Tipo atual: " . ($paymentData['billingType'] ?? 'unknown'));
            }
            
        } catch (\GuzzleHttp\Exception\RequestException $e) {
            Log::error('Erro ao verificar status do pagamento', [
                'payment_id' => $paymentId,
                'error' => $e->getMessage()
            ]);
            throw new \Exception('Erro ao verificar status do pagamento: ' . $e->getMessage());
        }
        
        $maxRetries = 2;
        $retryDelay = 2; // segundos
        
        for ($attempt = 1; $attempt <= $maxRetries; $attempt++) {
            try {
                Log::info('Tentativa de geração do QR Code', [
                    'payment_id' => $paymentId,
                    'attempt' => $attempt,
                    'max_retries' => $maxRetries
                ]);
                
                $response = $this->client->get("payments/{$paymentId}/pixQrCode");
                $result = json_decode($response->getBody()->getContents(), true);
                
                Log::info('QR Code PIX gerado com sucesso', [
                    'payment_id' => $paymentId,
                    'attempt' => $attempt,
                    'has_encoded_image' => !empty($result['encodedImage']),
                    'has_payload' => !empty($result['payload']),
                    'expiration_date' => $result['expirationDate'] ?? null
                ]);
                
                return $result;
                
            } catch (\GuzzleHttp\Exception\ConnectException $e) {
                Log::warning('Timeout na tentativa de gerar QR Code PIX', [
                    'error' => $e->getMessage(),
                    'payment_id' => $paymentId,
                    'attempt' => $attempt,
                    'max_retries' => $maxRetries
                ]);
                
                if ($attempt === $maxRetries) {
                    // Última tentativa falhou - lançar exceção específica
                    Log::error('Todas as tentativas de gerar QR Code falharam', [
                        'payment_id' => $paymentId,
                        'total_attempts' => $maxRetries
                    ]);
                    
                    throw new \Exception('A API do Asaas está temporariamente indisponível. Tente novamente em alguns minutos.');
                }
                
                // Aguardar antes da próxima tentativa
                sleep($retryDelay);
                continue;
                
            } catch (RequestException $e) {
                Log::error('Erro ao gerar QR Code PIX dinâmico no Asaas', [
                    'error' => $e->getMessage(),
                    'payment_id' => $paymentId,
                    'response_body' => $e->hasResponse() ? $e->getResponse()->getBody()->getContents() : null
                ]);
                throw $e;
            }
        }
    }
}