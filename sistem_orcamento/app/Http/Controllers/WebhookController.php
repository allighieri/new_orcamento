<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Models\Subscription;
use App\Models\UsageControl;
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
            // Capturar dados brutos da requisição
            $rawInput = $request->getContent();
            $headers = $request->headers->all();
            $method = $request->method();
            $url = $request->fullUrl();
            
            Log::info('Webhook Asaas recebido', [
                'method' => $method,
                'url' => $url,
                'content_length' => strlen($rawInput),
                'has_payload' => !empty($rawInput),
                'headers' => $headers,
                'raw_input' => $rawInput
            ]);
            
            // Tentar decodificar JSON
            $payload = [];
            if (!empty($rawInput)) {
                $payload = json_decode($rawInput, true);
                if (json_last_error() !== JSON_ERROR_NONE) {
                    Log::error('Erro ao decodificar JSON do webhook', [
                        'raw_input' => $rawInput,
                        'json_error' => json_last_error_msg()
                    ]);
                    return response()->json(['status' => 'error', 'message' => 'Invalid JSON'], 400);
                }
            } else {
                // Fallback para request->all() se não houver raw input
                $payload = $request->all();
            }
            
            Log::info('Payload decodificado', $payload);
            
            // Validar se é um evento de pagamento
            if (!isset($payload['event']) || !isset($payload['payment'])) {
                Log::warning('Webhook inválido - dados obrigatórios ausentes', [
                    'payload' => $payload,
                    'has_event' => isset($payload['event']),
                    'has_payment' => isset($payload['payment'])
                ]);
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
        
        // Verificar se é um pagamento de orçamentos extras
        if ($payment->extra_budgets_quantity && $payment->extra_budgets_quantity > 0) {
            $this->handleExtraBudgetsPayment($payment);
        } else {
            $this->handleSubscriptionPayment($payment, $paymentData);
        }
    }
    
    /**
     * Processar pagamento de orçamentos extras
     */
    private function handleExtraBudgetsPayment(Payment $payment)
    {
        // Buscar assinatura ativa da empresa
        $subscription = Subscription::where('company_id', $payment->company_id)
            ->where('status', 'active')
            ->first();
            
        if (!$subscription) {
            Log::error('Assinatura ativa não encontrada para pagamento de orçamentos extras', [
                'payment_id' => $payment->id,
                'company_id' => $payment->company_id
            ]);
            return;
        }
        
        // Buscar controle de uso atual
        $usageControl = UsageControl::getOrCreateForCurrentMonth(
            $payment->company_id,
            $subscription->id,
            $subscription->plan->budget_limit
        );
        
        // Adicionar orçamentos extras ao limite atual (mesmo valor do plano)
        $extraBudgets = $subscription->plan->budget_limit;
        $usageControl->addExtraBudgets($extraBudgets, $payment->amount);
        
        Log::info('Orçamentos extras adicionados via pagamento', [
            'payment_id' => $payment->id,
            'company_id' => $payment->company_id,
            'subscription_id' => $subscription->id,
            'extra_budgets_added' => $extraBudgets,
            'total_extra_budgets' => $usageControl->extra_budgets_purchased,
            'budgets_limit' => $usageControl->budgets_limit,
            'amount_paid' => $payment->amount
        ]);
    }
    
    /**
     * Processar pagamento de assinatura
     */
    private function handleSubscriptionPayment(Payment $payment, array $paymentData = [])
    {
        // Buscar assinatura ativa da empresa
        $subscription = Subscription::where('company_id', $payment->company_id)
            ->where('status', 'active')
            ->first();
        
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
        $newSubscription = Subscription::create([
            'company_id' => $payment->company_id,
            'plan_id' => $payment->plan_id,
            'billing_cycle' => $payment->billing_cycle,
            'status' => 'active',
            'start_date' => $startDate,
            'end_date' => $endDate,
            'next_billing_date' => $endDate,
            'amount_paid' => $payment->amount,
            'created_at' => now(),
            'updated_at' => now()
        ]);
        
        // Processar controle de uso para o novo plano
        $plan = \App\Models\Plan::find($payment->plan_id);
        
        if ($plan) {
            // Buscar controle de uso atual para calcular orçamentos restantes
            $currentUsageControl = null;
            if ($subscription) {
                $currentUsageControl = UsageControl::where('company_id', $payment->company_id)
                    ->where('year', now()->year)
                    ->where('month', now()->month)
                    ->first();
            }
            
            // Calcular todos os orçamentos restantes do plano anterior
            $remainingBudgetsToMigrate = 0;
            if ($currentUsageControl) {
                $oldPlanLimit = $currentUsageControl->budgets_limit;
                $extraBudgetsPurchased = $currentUsageControl->extra_budgets_purchased;
                $usedBudgets = $currentUsageControl->budgets_used;
                $totalOldBudgets = $oldPlanLimit + $extraBudgetsPurchased;
                
                // Calcular todos os orçamentos restantes (base + extras não utilizados)
                if ($usedBudgets < $totalOldBudgets) {
                    $remainingBudgetsToMigrate = $totalOldBudgets - $usedBudgets;
                }
                
                Log::info('Calculando orçamentos restantes na mudança de plano', [
                    'company_id' => $payment->company_id,
                    'old_plan_limit' => $oldPlanLimit,
                    'old_extra_purchased' => $extraBudgetsPurchased,
                    'used_budgets' => $usedBudgets,
                    'total_old_budgets' => $totalOldBudgets,
                    'remaining_budgets_to_migrate' => $remainingBudgetsToMigrate,
                    'new_plan_limit' => $plan->budget_limit
                ]);
            }
            
            // Criar ou atualizar controle de uso com o novo plano
            $usageControl = UsageControl::getOrCreateForCurrentMonth(
                $payment->company_id,
                $newSubscription->id,
                $plan->budget_limit
            );
            
            // Resetar budgets_used para 0 e migrar orçamentos restantes como extras
            $usageControl->budgets_used = 0;
            $usageControl->extra_budgets_purchased = $remainingBudgetsToMigrate;
            $usageControl->save();
            
            if ($remainingBudgetsToMigrate > 0) {
                Log::info('Orçamentos restantes migrados para novo plano', [
                    'company_id' => $payment->company_id,
                    'subscription_id' => $newSubscription->id,
                    'migrated_budgets' => $remainingBudgetsToMigrate,
                    'new_plan_limit' => $plan->budget_limit,
                    'total_available' => $plan->budget_limit + $remainingBudgetsToMigrate
                ]);
            }
        } else {
            Log::warning('Plano não encontrado, usando valor padrão', [
                'payment_id' => $payment->id,
                'plan_id' => $payment->plan_id
            ]);
            
            UsageControl::getOrCreateForCurrentMonth(
                $payment->company_id,
                $newSubscription->id,
                10
            );
        }
        
        // Sinalizar para a página de checkout que o pagamento foi aprovado
        $cacheData = [
            'approved_at' => now()->toISOString(),
            'status' => $paymentData['status'] ?? $payment->status,
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
            'asaas_status' => $paymentData['status'] ?? $payment->status
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
