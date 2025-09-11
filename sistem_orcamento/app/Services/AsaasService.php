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
            'timeout' => 30
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
                'status_code' => $response->getStatusCode()
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
        try {
            Log::info('Gerando QR Code PIX dinâmico', ['payment_id' => $paymentId]);
            
            $response = $this->client->get("payments/{$paymentId}/pixQrCode");
            
            $result = json_decode($response->getBody()->getContents(), true);
            
            Log::info('QR Code PIX dinâmico gerado com sucesso', [
                'payment_id' => $paymentId,
                'has_encoded_image' => !empty($result['encodedImage']),
                'has_payload' => !empty($result['payload']),
                'expiration_date' => $result['expirationDate'] ?? null
            ]);
            
            return $result;
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