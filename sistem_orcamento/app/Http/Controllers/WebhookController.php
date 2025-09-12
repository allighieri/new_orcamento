<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Models\Subscription;
use App\Services\AsaasService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class WebhookController extends Controller
{
    protected $asaasService;

    public function __construct(AsaasService $asaasService)
    {
        $this->asaasService = $asaasService;
    }

    /**
     * Processar webhook do Asaas
     */
    public function handleAsaasWebhook(Request $request)
    {
        try {
            $payload = $request->all();
            
            Log::info('Webhook Asaas recebido', $payload);
            
            // Validar se é um evento de pagamento
            if (!isset($payload['event']) || !isset($payload['payment'])) {
                Log::warning('Webhook inválido - dados obrigatórios ausentes', $payload);
                return response()->json(['status' => 'error', 'message' => 'Invalid webhook data'], 400);
            }
            
            $event = $payload['event'];
            $paymentData = $payload['payment'];
            $asaasPaymentId = $paymentData['id'];
            
            // Buscar pagamento no banco de dados
            $payment = Payment::where('asaas_payment_id', $asaasPaymentId)->first();
            
            if (!$payment) {
                Log::warning('Pagamento não encontrado no banco', ['asaas_payment_id' => $asaasPaymentId]);
                return response()->json(['status' => 'error', 'message' => 'Payment not found'], 404);
            }
            
            DB::beginTransaction();
            
            // Processar diferentes tipos de eventos
            switch ($event) {
                case 'PAYMENT_RECEIVED':
                case 'PAYMENT_CONFIRMED':
                    $this->handlePaymentApproved($payment, $paymentData);
                    break;
                    
                case 'PAYMENT_OVERDUE':
                    $this->handlePaymentOverdue($payment, $paymentData);
                    break;
                    
                case 'PAYMENT_DELETED':
                case 'PAYMENT_REFUNDED':
                    $this->handlePaymentCancelled($payment, $paymentData);
                    break;
                    
                default:
                    // Atualizar apenas o status para outros eventos
                    $payment->updateStatus($paymentData['status'], $payload);
                    Log::info('Status do pagamento atualizado', [
                        'payment_id' => $payment->id,
                        'event' => $event,
                        'status' => $paymentData['status']
                    ]);
            }
            
            DB::commit();
            
            return response()->json(['status' => 'success'], 200);
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erro ao processar webhook do Asaas', [
                'error' => $e->getMessage(),
                'payload' => $request->all()
            ]);
            
            return response()->json(['status' => 'error', 'message' => 'Internal server error'], 500);
        }
    }
    
    /**
     * Processar pagamento aprovado
     */
    private function handlePaymentApproved(Payment $payment, array $paymentData)
    {
        // Atualizar status do pagamento
        $payment->updateStatus($paymentData['status'], $paymentData);
        
        // Criar ou atualizar assinatura da empresa
        $subscription = Subscription::where('company_id', $payment->company_id)->first();
        
        // Cancelar assinatura atual se existir
        if ($subscription && $subscription->status === 'active') {
            $subscription->update([
                'status' => 'cancelled',
                'cancelled_at' => now()
            ]);
        }
        
        // Calcular datas baseado no ciclo de cobrança
        $startDate = now();
        $endDate = $payment->billing_cycle === 'annual' 
            ? $startDate->copy()->addYear()
            : $startDate->copy()->addMonth();
            
        // Criar nova assinatura
        Subscription::create([
            'company_id' => $payment->company_id,
            'plan_id' => $payment->plan_id,
            'billing_cycle' => $payment->billing_cycle,
            'status' => 'active',
            'start_date' => $startDate,
            'end_date' => $endDate,
            'next_billing_date' => $endDate,
            'amount_paid' => $payment->amount
        ]);
        
        // Sinalizar para a página de checkout que o pagamento foi aprovado
        $cacheData = [
            'approved_at' => now()->toISOString(),
            'status' => $paymentData['status'],
            'webhook_event' => 'PAYMENT_APPROVED',
            'payment_id' => $payment->id
        ];
        
        \Illuminate\Support\Facades\Cache::put(
            "payment_approved_{$payment->id}", 
            $cacheData, 
            now()->addMinutes(15)
        );
        
        Log::info('Pagamento aprovado via webhook - Cache definido', [
            'payment_id' => $payment->id,
            'company_id' => $payment->company_id,
            'plan_id' => $payment->plan_id,
            'cache_key' => "payment_approved_{$payment->id}",
            'cache_expires_at' => now()->addMinutes(15)->toISOString(),
            'asaas_status' => $paymentData['status']
        ]);
        
        Log::info('Pagamento aprovado e assinatura ativada', [
            'payment_id' => $payment->id,
            'company_id' => $payment->company_id,
            'plan_id' => $payment->plan_id
        ]);
    }
    
    /**
     * Processar pagamento vencido
     */
    private function handlePaymentOverdue(Payment $payment, array $paymentData)
    {
        // Atualizar status do pagamento
        $payment->updateStatus($paymentData['status'], $paymentData);
        
        // Suspender assinatura se existir
        $subscription = Subscription::where('company_id', $payment->company_id)->first();
        
        if ($subscription && $subscription->status === 'active') {
            $subscription->update([
                'status' => 'suspended',
                'updated_at' => now()
            ]);
            
            Log::info('Assinatura suspensa por pagamento vencido', [
                'payment_id' => $payment->id,
                'company_id' => $payment->company_id,
                'subscription_id' => $subscription->id
            ]);
        }
    }
    
    /**
     * Processar pagamento cancelado/estornado
     */
    private function handlePaymentCancelled(Payment $payment, array $paymentData)
    {
        // Atualizar status do pagamento
        $payment->updateStatus($paymentData['status'], $paymentData);
        
        // Cancelar assinatura se existir
        $subscription = Subscription::where('company_id', $payment->company_id)->first();
        
        if ($subscription) {
            $subscription->update([
                'status' => 'cancelled',
                'ends_at' => now(),
                'updated_at' => now()
            ]);
            
            Log::info('Assinatura cancelada por pagamento estornado', [
                'payment_id' => $payment->id,
                'company_id' => $payment->company_id,
                'subscription_id' => $subscription->id
            ]);
        }
    }
}
