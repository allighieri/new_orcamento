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
            // Validar e formatar dados antes de enviar
            $validatedData = $this->validateAndFormatCustomerData($data);
            
            Log::info('Dados do cliente validados e formatados', [
                'original' => $data,
                'validated' => $validatedData
            ]);
            
            $response = $this->client->post('customers', [
                'json' => $validatedData
            ]);

            return json_decode($response->getBody()->getContents(), true);
        } catch (RequestException $e) {
            Log::error('Erro ao criar cliente no Asaas', [
                'error' => $e->getMessage(),
                'data' => $data,
                'response_body' => $e->hasResponse() ? $e->getResponse()->getBody()->getContents() : null
            ]);
            throw $e;
        }
    }
    
    /**
     * Validar e formatar dados do cliente para o Asaas
     */
    private function validateAndFormatCustomerData($data)
    {
        $validated = [];
        
        // Nome é obrigatório
        if (empty($data['name'])) {
            throw new \InvalidArgumentException('Nome é obrigatório');
        }
        $validated['name'] = trim($data['name']);
        
        // Email é obrigatório e deve ser válido
        if (empty($data['email']) || !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            throw new \InvalidArgumentException('Email válido é obrigatório');
        }
        $validated['email'] = trim(strtolower($data['email']));
        
        // CPF/CNPJ é obrigatório e deve ser válido
        if (empty($data['cpfCnpj'])) {
            throw new \InvalidArgumentException('CPF/CNPJ é obrigatório');
        }
        
        $cpfCnpj = $this->formatCpfCnpj($data['cpfCnpj']);
        if (!$this->isValidCpfCnpj($cpfCnpj)) {
            throw new \InvalidArgumentException('CPF/CNPJ inválido: ' . $data['cpfCnpj']);
        }
        $validated['cpfCnpj'] = $cpfCnpj;
        
        // Telefone é opcional - só incluir se fornecido e válido
         if (!empty($data['phone'])) {
             $phone = $this->formatPhone($data['phone']);
             if ($this->isValidPhone($phone)) {
                 $validated['phone'] = $phone;
             } else {
                 // Log do telefone inválido mas não falha - apenas não inclui
                 Log::warning('Telefone inválido fornecido, será ignorado', [
                     'original_phone' => $data['phone'],
                     'formatted_phone' => $phone
                 ]);
             }
         }
        
        return $validated;
    }
    
    /**
     * Formatar CPF/CNPJ removendo caracteres especiais
     */
    private function formatCpfCnpj($cpfCnpj)
    {
        return preg_replace('/[^0-9]/', '', $cpfCnpj);
    }
    
    /**
     * Validar CPF/CNPJ
     */
    private function isValidCpfCnpj($cpfCnpj)
    {
        $cpfCnpj = $this->formatCpfCnpj($cpfCnpj);
        
        if (strlen($cpfCnpj) == 11) {
            return $this->isValidCpf($cpfCnpj);
        } elseif (strlen($cpfCnpj) == 14) {
            return $this->isValidCnpj($cpfCnpj);
        }
        
        return false;
    }
    
    /**
     * Validar CPF
     */
    private function isValidCpf($cpf)
    {
        // Verificar se tem 11 dígitos
        if (strlen($cpf) != 11) {
            return false;
        }
        
        // Verificar se todos os dígitos são iguais
        if (preg_match('/^(\d)\1{10}$/', $cpf)) {
            return false;
        }
        
        // Calcular primeiro dígito verificador
        $sum = 0;
        for ($i = 0; $i < 9; $i++) {
            $sum += intval($cpf[$i]) * (10 - $i);
        }
        $remainder = $sum % 11;
        $digit1 = ($remainder < 2) ? 0 : 11 - $remainder;
        
        // Verificar primeiro dígito
        if (intval($cpf[9]) != $digit1) {
            return false;
        }
        
        // Calcular segundo dígito verificador
        $sum = 0;
        for ($i = 0; $i < 10; $i++) {
            $sum += intval($cpf[$i]) * (11 - $i);
        }
        $remainder = $sum % 11;
        $digit2 = ($remainder < 2) ? 0 : 11 - $remainder;
        
        // Verificar segundo dígito
        return intval($cpf[10]) == $digit2;
    }
    
    /**
     * Validar CNPJ
     */
    private function isValidCnpj($cnpj)
    {
        // Verificar se tem 14 dígitos
        if (strlen($cnpj) != 14) {
            return false;
        }
        
        // Verificar se todos os dígitos são iguais
        if (preg_match('/^(\d)\1{13}$/', $cnpj)) {
            return false;
        }
        
        // Calcular primeiro dígito verificador
        $weights1 = [5, 4, 3, 2, 9, 8, 7, 6, 5, 4, 3, 2];
        $sum = 0;
        for ($i = 0; $i < 12; $i++) {
            $sum += intval($cnpj[$i]) * $weights1[$i];
        }
        $remainder = $sum % 11;
        $digit1 = ($remainder < 2) ? 0 : 11 - $remainder;
        
        // Verificar primeiro dígito
        if (intval($cnpj[12]) != $digit1) {
            return false;
        }
        
        // Calcular segundo dígito verificador
        $weights2 = [6, 5, 4, 3, 2, 9, 8, 7, 6, 5, 4, 3, 2];
        $sum = 0;
        for ($i = 0; $i < 13; $i++) {
            $sum += intval($cnpj[$i]) * $weights2[$i];
        }
        $remainder = $sum % 11;
        $digit2 = ($remainder < 2) ? 0 : 11 - $remainder;
        
        // Verificar segundo dígito
        return intval($cnpj[13]) == $digit2;
    }
    
    /**
     * Formatar telefone para o padrão do Asaas
     */
    private function formatPhone($phone)
    {
        // Remover caracteres especiais
        $phone = preg_replace('/[^0-9]/', '', $phone);
        
        // Se começar com 0, remover
        if (substr($phone, 0, 1) === '0') {
            $phone = substr($phone, 1);
        }
        
        // Formato esperado pelo Asaas: apenas números, sem código do país
        // Exemplo: 11999999999 (DDD + número)
        if (strlen($phone) === 10) {
            // Telefone fixo: adicionar 9 no início para ficar com 11 dígitos
            $phone = substr($phone, 0, 2) . '9' . substr($phone, 2);
        }
        
        return $phone;
    }
    
    /**
     * Validar telefone
     */
    private function isValidPhone($phone)
    {
        $phone = preg_replace('/[^0-9]/', '', $phone);
        
        // Deve ter 10 ou 11 dígitos (DDD + número)
        if (strlen($phone) !== 10 && strlen($phone) !== 11) {
            return false;
        }
        
        // DDD deve estar entre 11 e 99
        $ddd = intval(substr($phone, 0, 2));
        if ($ddd < 11 || $ddd > 99) {
            return false;
        }
        
        return true;
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

    // Método removido - não criamos mais assinaturas recorrentes no Asaas

    /**
     * Cancelar assinatura no Asaas
     */
    public function cancelSubscription($subscriptionId)
    {
        try {
            Log::info('Cancelando assinatura no Asaas', ['subscription_id' => $subscriptionId]);
            
            $response = $this->client->delete("subscriptions/{$subscriptionId}");
            $responseData = json_decode($response->getBody()->getContents(), true);
            
            Log::info('Assinatura cancelada com sucesso', [
                'subscription_id' => $subscriptionId,
                'response' => $responseData
            ]);

            return $responseData;
        } catch (RequestException $e) {
            Log::error('Erro ao cancelar assinatura no Asaas', [
                'error' => $e->getMessage(),
                'subscription_id' => $subscriptionId,
                'response_body' => $e->hasResponse() ? $e->getResponse()->getBody()->getContents() : null
            ]);
            throw $e;
        }
    }

    /**
     * Buscar informações da assinatura
     */
    public function getSubscription($subscriptionId)
    {
        try {
            $response = $this->client->get("subscriptions/{$subscriptionId}");
            return json_decode($response->getBody()->getContents(), true);
        } catch (RequestException $e) {
            Log::error('Erro ao buscar assinatura no Asaas', [
                'error' => $e->getMessage(),
                'subscription_id' => $subscriptionId
            ]);
            throw $e;
        }
    }

    /**
     * Buscar informações de parcelamento
     */
    public function getInstallment($installmentId)
    {
        try {
            $response = $this->client->get("installments/{$installmentId}");
            return json_decode($response->getBody()->getContents(), true);
        } catch (RequestException $e) {
            Log::error('Erro ao buscar parcelamento no Asaas', [
                'error' => $e->getMessage(),
                'installment_id' => $installmentId
            ]);
            throw $e;
        }
    }

    /**
     * Atualizar assinatura no Asaas
     */
    public function updateSubscription($subscriptionId, $data)
    {
        try {
            Log::info('Atualizando assinatura no Asaas', [
                'subscription_id' => $subscriptionId,
                'data' => $data
            ]);
            
            $response = $this->client->post("subscriptions/{$subscriptionId}", [
                'json' => $data
            ]);

            $responseData = json_decode($response->getBody()->getContents(), true);
            
            Log::info('Assinatura atualizada com sucesso', [
                'subscription_id' => $subscriptionId,
                'response' => $responseData
            ]);

            return $responseData;
        } catch (RequestException $e) {
            Log::error('Erro ao atualizar assinatura no Asaas', [
                'error' => $e->getMessage(),
                'subscription_id' => $subscriptionId,
                'data' => $data,
                'response_body' => $e->hasResponse() ? $e->getResponse()->getBody()->getContents() : null
            ]);
            throw $e;
        }
    }

    /**
     * Buscar cobranças de uma assinatura
     */
    public function getSubscriptionPayments($subscriptionId)
    {
        try {
            Log::info('Buscando cobranças da assinatura no Asaas', ['subscription_id' => $subscriptionId]);
            
            $response = $this->client->get("subscriptions/{$subscriptionId}/payments");
            $responseData = json_decode($response->getBody()->getContents(), true);
            
            Log::info('Cobranças da assinatura encontradas', [
                'subscription_id' => $subscriptionId,
                'total_count' => $responseData['totalCount'] ?? 0
            ]);

            return $responseData;
        } catch (RequestException $e) {
            Log::error('Erro ao buscar cobranças da assinatura no Asaas', [
                'error' => $e->getMessage(),
                'subscription_id' => $subscriptionId,
                'response_body' => $e->hasResponse() ? $e->getResponse()->getBody()->getContents() : null
            ]);
            throw $e;
        }
    }

    /**
     * Cancelar pagamento no Asaas
     */
    public function cancelPayment($paymentId)
    {
        try {
            Log::info('Cancelando pagamento no Asaas', ['payment_id' => $paymentId]);
            
            $response = $this->client->delete("payments/{$paymentId}");
            $responseData = json_decode($response->getBody()->getContents(), true);
            
            Log::info('Pagamento cancelado com sucesso', [
                'payment_id' => $paymentId,
                'response' => $responseData
            ]);

            return $responseData;
        } catch (RequestException $e) {
            Log::error('Erro ao cancelar pagamento no Asaas', [
                'error' => $e->getMessage(),
                'payment_id' => $paymentId,
                'response_body' => $e->hasResponse() ? $e->getResponse()->getBody()->getContents() : null
            ]);
            throw $e;
        }
    }
}