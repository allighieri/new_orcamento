<?php

namespace App\Jobs;

use App\Events\PaymentConfirmed;
use App\Models\Payment;
use App\Models\Plan;
use App\Models\Subscription;
use App\Services\AsaasService;
use App\Services\PlanUpgradeService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ProcessAsaasWebhook implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $payload;
    public $tries = 3; // Número de tentativas em caso de falha
    public $timeout = 120; // Timeout em segundos

    /**
     * Create a new job instance.
     */
    public function __construct(array $payload)
    {
        $this->payload = $payload;
    }

    /**
     * Execute the job.
     */
    public function handle(AsaasService $asaasService, PlanUpgradeService $planUpgradeService)
    {
        try {
            Log::info('Processando webhook Asaas via fila', [
                'job_id' => $this->job ? $this->job->getJobId() : 'direct_execution',
                'payload' => $this->payload
            ]);

            // Validar se é um evento válido
            if (!isset($this->payload['event'])) {
                Log::warning('Webhook inválido - evento ausente', [
                    'payload' => $this->payload
                ]);
                return;
            }

            $event = $this->payload['event'];

            // Processar diferentes tipos de eventos
            if (str_starts_with($event, 'PAYMENT_')) {
                $this->handlePaymentEvent($planUpgradeService);
            } elseif (str_starts_with($event, 'SUBSCRIPTION_')) {
                $this->handleSubscriptionEvent();
            } else {
                Log::info('Evento não processado', [
                    'event' => $event,
                    'payload' => $this->payload
                ]);
            }

            Log::info('Webhook processado com sucesso via fila', [
                'event' => $event
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erro ao processar webhook via fila', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'payload' => $this->payload
            ]);
            
            // Re-lançar a exceção para que o Laravel tente novamente
            throw $e;
        }
    }

    /**
     * Processar eventos de pagamento
     */
    private function handlePaymentEvent(PlanUpgradeService $planUpgradeService)
    {
        if (!isset($this->payload['payment'])) {
            Log::warning('Evento de pagamento sem dados de pagamento', [
                'event' => $this->payload['event'],
                'payload' => $this->payload
            ]);
            return;
        }

        $event = $this->payload['event'];
        $paymentData = $this->payload['payment'];
        $asaasPaymentId = $paymentData['id'];

        // Buscar pagamento no banco de dados
        $payment = Payment::where('asaas_payment_id', $asaasPaymentId)->first();

        // Se não encontrou por payment_id, tentar por subscription_id (para assinaturas)
        if (!$payment && isset($paymentData['subscription'])) {
            $asaasSubscriptionId = $paymentData['subscription'];
            $payment = Payment::where('asaas_subscription_id', $asaasSubscriptionId)->first();
            
            Log::info('Buscando pagamento por subscription_id', [
                'asaas_payment_id' => $asaasPaymentId,
                'asaas_subscription_id' => $asaasSubscriptionId,
                'payment_found' => $payment ? 'sim' : 'não'
            ]);
        }

        // Atualizar campos se o pagamento foi encontrado
        if ($payment) {
            // Atualizar asaas_subscription_id se veio no webhook e não está preenchido
            if (isset($paymentData['subscription']) && !$payment->asaas_subscription_id) {
                $payment->update(['asaas_subscription_id' => $paymentData['subscription']]);
                Log::info('Campo asaas_subscription_id atualizado via webhook', [
                    'payment_id' => $payment->id,
                    'asaas_subscription_id' => $paymentData['subscription']
                ]);
            }
            
            // Atualizar payment_id se não está preenchido (usar o próprio ID do payment)
            if (!$payment->payment_id) {
                $payment->update(['payment_id' => $payment->id]);
                Log::info('Campo payment_id atualizado via webhook', [
                    'payment_id' => $payment->id
                ]);
            }
        }

        if (!$payment) {
            Log::warning('Pagamento não encontrado no banco', [
                'asaas_payment_id' => $asaasPaymentId,
                'asaas_subscription_id' => $paymentData['subscription'] ?? null
            ]);
            return;
        }

        DB::beginTransaction();

        // Processar diferentes tipos de eventos de pagamento
        switch ($event) {
            case 'PAYMENT_RECEIVED':
                // Para PAYMENT_RECEIVED, processar com prioridade alta para melhor responsividade
                $this->handlePaymentReceived($payment, $paymentData, $planUpgradeService);
                break;
                
            case 'PAYMENT_CONFIRMED':
                $this->handlePaymentApproved($payment, $paymentData, $planUpgradeService);
                break;
                
            case 'PAYMENT_OVERDUE':
                $this->handlePaymentOverdue($payment, $paymentData);
                break;
                
            case 'PAYMENT_DELETED':
            case 'PAYMENT_REFUNDED':
            case 'PAYMENT_CANCELLED':
                $this->handlePaymentCancelled($payment, $paymentData);
                break;
                
            default:
                // Atualizar apenas o status para outros eventos
                $payment->updateStatus($paymentData['status'], $this->payload);
                Log::info('Status do pagamento atualizado via fila', [
                    'payment_id' => $payment->id,
                    'event' => $event,
                    'status' => $paymentData['status']
                ]);
        }

        DB::commit();
    }

    /**
     * Processar eventos de assinatura
     */
    private function handleSubscriptionEvent()
    {
        if (!isset($this->payload['subscription'])) {
            Log::warning('Evento de assinatura sem dados de assinatura', [
                'event' => $this->payload['event'],
                'payload' => $this->payload
            ]);
            return;
        }

        $event = $this->payload['event'];
        $subscriptionData = $this->payload['subscription'];
        $asaasSubscriptionId = $subscriptionData['id'];

        Log::info('Processando evento de assinatura via fila', [
            'event' => $event,
            'asaas_subscription_id' => $asaasSubscriptionId,
            'subscription_data' => $subscriptionData
        ]);

        DB::beginTransaction();

        switch ($event) {
            case 'SUBSCRIPTION_CREATED':
                $this->handleSubscriptionCreated($subscriptionData);
                break;
                
            case 'SUBSCRIPTION_UPDATED':
                $this->handleSubscriptionUpdated($subscriptionData);
                break;
                
            case 'SUBSCRIPTION_DELETED':
            case 'SUBSCRIPTION_CANCELLED':
                $this->handleSubscriptionCancelled($subscriptionData);
                break;
                
            default:
                Log::info('Evento de assinatura não processado', [
                    'event' => $event,
                    'asaas_subscription_id' => $asaasSubscriptionId
                ]);
        }

        DB::commit();
    }

    /**
     * Processar criação de assinatura
     */
    private function handleSubscriptionCreated($subscriptionData)
    {
        $asaasSubscriptionId = $subscriptionData['id'];
        
        // Verificar se já existe uma assinatura com este ID
        $existingSubscription = Subscription::where('asaas_subscription_id', $asaasSubscriptionId)->first();
        
        if ($existingSubscription) {
            Log::info('Assinatura já existe no banco', [
                'asaas_subscription_id' => $asaasSubscriptionId,
                'subscription_id' => $existingSubscription->id
            ]);
            return;
        }

        Log::info('Nova assinatura criada no Asaas - aguardando primeiro pagamento', [
            'asaas_subscription_id' => $asaasSubscriptionId,
            'customer' => $subscriptionData['customer'] ?? null,
            'value' => $subscriptionData['value'] ?? null,
            'cycle' => $subscriptionData['cycle'] ?? null,
            'status' => $subscriptionData['status'] ?? null
        ]);
    }

    /**
     * Processar atualização de assinatura
     */
    private function handleSubscriptionUpdated($subscriptionData)
    {
        $asaasSubscriptionId = $subscriptionData['id'];
        
        $subscription = Subscription::where('asaas_subscription_id', $asaasSubscriptionId)->first();
        
        if ($subscription) {
            Log::info('Assinatura atualizada no Asaas', [
                'asaas_subscription_id' => $asaasSubscriptionId,
                'subscription_id' => $subscription->id,
                'new_status' => $subscriptionData['status'] ?? null
            ]);
        } else {
            Log::warning('Assinatura para atualização não encontrada', [
                'asaas_subscription_id' => $asaasSubscriptionId
            ]);
        }
    }

    /**
     * Processar cancelamento de assinatura
     */
    private function handleSubscriptionCancelled($subscriptionData)
    {
        $asaasSubscriptionId = $subscriptionData['id'];
        
        $subscription = Subscription::where('asaas_subscription_id', $asaasSubscriptionId)->first();
        
        if ($subscription) {
            $subscription->update([
                'status' => 'cancelled',
                'cancelled_at' => now()
            ]);
            
            Log::info('Assinatura cancelada via webhook', [
                'asaas_subscription_id' => $asaasSubscriptionId,
                'subscription_id' => $subscription->id
            ]);
        } else {
            Log::warning('Assinatura para cancelamento não encontrada', [
                'asaas_subscription_id' => $asaasSubscriptionId
            ]);
        }


    }

    /**
     * Processar pagamento recebido (PAYMENT_RECEIVED) - Otimizado para responsividade
     */
    private function handlePaymentReceived(Payment $payment, array $paymentData, PlanUpgradeService $planUpgradeService)
    {
        // Atualizar status imediatamente para melhor responsividade
        $payment->updateStatus($paymentData['status'], $this->payload);
        
        // Definir confirmed_at se ainda não estiver definido
        if (!$payment->confirmed_at) {
            $payment->update(['confirmed_at' => now()]);
        }
        
        // Disparar evento imediatamente para atualização em tempo real
        $planType = null;
        if ($payment->plan_id) {
            $plan = Plan::find($payment->plan_id);
            $planType = $plan ? $plan->name : null;
        }
        
        event(new PaymentConfirmed(
            $payment->id,
            $payment->status,
            $payment->company_id,
            $planType,
            $payment->amount
        ));
        
        Log::info('Evento PaymentConfirmed disparado (RECEIVED)', [
            'payment_id' => $payment->id,
            'status' => $payment->status,
            'company_id' => $payment->company_id,
            'plan_type' => $planType,
            'amount' => $payment->amount
        ]);
        
        // Processar lógica de negócio em segundo plano (sem afetar responsividade)
        $this->processPaymentBusinessLogic($payment, $paymentData, $planUpgradeService);
    }
    
    /**
     * Processar pagamento aprovado (PAYMENT_CONFIRMED)
     */
    private function handlePaymentApproved(Payment $payment, array $paymentData, PlanUpgradeService $planUpgradeService)
    {
        // Atualizar status do pagamento
        $payment->updateStatus($paymentData['status'], $this->payload);
        
        // Processar lógica de negócio
        $this->processPaymentBusinessLogic($payment, $paymentData, $planUpgradeService);
    }
    
    /**
     * Processar lógica de negócio do pagamento (compartilhada entre RECEIVED e CONFIRMED)
     */
    private function processPaymentBusinessLogic(Payment $payment, array $paymentData, PlanUpgradeService $planUpgradeService)
    {
        // Normalizar billing_cycle para garantir valores válidos
        $billingCycleForSubscription = match($payment->billing_cycle) {
            'yearly' => 'yearly',
            'monthly' => 'monthly',
            default => 'monthly'
        };
        
        // Atualizar status do pagamento e corrigir valor se necessário
        $updateData = ['status' => strtolower($paymentData['status'])];
        
        // Para pagamentos de planos mensais/anuais, garantir que o valor seja o valor total do plano
        if ($payment->plan_id && in_array($payment->type, ['subscription', 'plan_change', null])) {
            $plan = \App\Models\Plan::find($payment->plan_id);
            if ($plan) {
                $correctAmount = $billingCycleForSubscription === 'yearly' ? $plan->yearly_price : $plan->monthly_price;
                if (abs($payment->amount - $correctAmount) > 0.01) {
                    $updateData['amount'] = $correctAmount;
                    Log::info('Valor do pagamento corrigido no webhook', [
                        'payment_id' => $payment->id,
                        'old_amount' => $payment->amount,
                        'new_amount' => $correctAmount,
                        'plan_id' => $plan->id,
                        'billing_cycle' => $billingCycleForSubscription
                    ]);
                }
            }
        }
        
        $payment->update($updateData);
        
        if (isset($paymentData) && $paymentData) {
            $payment->update(['asaas_response' => $paymentData]);
        }
        
        if (in_array(strtolower($paymentData['status']), ['confirmed', 'received']) && !$payment->confirmed_at) {
            $payment->update(['confirmed_at' => now()]);
        }
        
        Log::info('DEBUG: Iniciando processamento de pagamento aprovado', [
            'payment_id' => $payment->id,
            'company_id' => $payment->company_id,
            'plan_id' => $payment->plan_id,
            'billing_cycle' => $billingCycleForSubscription,
            'amount' => $payment->amount,
            'current_type' => $payment->type
        ]);
        
        // Simplificar: todos os pagamentos são tratados como subscriptions
        // Não há mais assinaturas recorrentes, apenas pagamentos únicos
        $this->handleSubscriptionPayment($payment, $paymentData);
    }




    /**
     * Processar pagamento de assinatura
     */
    private function handleSubscriptionPayment(Payment $payment, array $paymentData = [])
    {
        // Definir o ciclo de cobrança baseado no payment->billing_cycle
        $billingCycleForSubscription = match($payment->billing_cycle) {
            'yearly' => 'yearly',
            'monthly' => 'monthly',
            default => 'monthly'
        };

        Log::info('Processando pagamento como subscription simples', [
            'payment_id' => $payment->id,
            'company_id' => $payment->company_id,
            'billing_cycle' => $billingCycleForSubscription,
            'amount' => $payment->amount
        ]);
        
        // Buscar e cancelar TODAS as assinaturas ativas da empresa antes de criar nova
        // Usar lockForUpdate para evitar condições de corrida
        $activeSubscriptions = Subscription::where('company_id', $payment->company_id)
            ->where('status', 'active')
            ->lockForUpdate()
            ->get();
        
        // Cancelar todas as assinaturas ativas encontradas
        foreach ($activeSubscriptions as $activeSubscription) {
            Log::info('Cancelando assinatura anterior (fila)', [
                'payment_id' => $payment->id,
                'old_subscription_id' => $activeSubscription->id,
                'old_plan_id' => $activeSubscription->plan_id,
                'new_plan_id' => $payment->plan_id,
                'old_billing_cycle' => $activeSubscription->billing_cycle,
                 'new_billing_cycle' => $billingCycleForSubscription
            ]);
            
            // Cancelar no Asaas se tiver subscription_id
            if ($activeSubscription->asaas_subscription_id) {
                try {
                    $asaasService = app(\App\Services\AsaasService::class);
                    $asaasService->cancelSubscription($activeSubscription->asaas_subscription_id);
                } catch (\Exception $e) {
                    Log::warning('Erro ao cancelar assinatura no Asaas via webhook', [
                        'subscription_id' => $activeSubscription->asaas_subscription_id,
                        'error' => $e->getMessage()
                    ]);
                }
            }
            
            $activeSubscription->update([
                'status' => 'cancelled',
                'cancelled_at' => now(),
                'end_date' => now()
            ]);
        }
        
        // Criar nova assinatura
        $plan = \App\Models\Plan::find($payment->plan_id);
        if (!$plan) {
            Log::error('Plano não encontrado para criar assinatura', [
                'payment_id' => $payment->id,
                'plan_id' => $payment->plan_id
            ]);
            return;
        }
        
        // Converter billing_cycle para o formato correto da tabela subscriptions
         // Já normalizado no início do método
        
        // Determinar datas baseadas no ciclo de cobrança
        $startDate = now();
        $endDate = $billingCycleForSubscription === 'yearly' ? $startDate->copy()->addYear() : $startDate->copy()->addMonth();
        $nextBillingDate = $billingCycleForSubscription === 'yearly' ? $endDate : $startDate->copy()->addMonth();
        $gracePeriodEndDate = $endDate->copy()->addDays(3); // 3 dias de período de graça
        
        // Criar nova assinatura
        $newSubscription = \App\Models\Subscription::create([
            'company_id' => $payment->company_id,
            'plan_id' => $plan->id,
            'status' => 'active',
            'start_date' => $startDate,
            'end_date' => $endDate,
            'starts_at' => $startDate,
            'ends_at' => $endDate,
            'grace_period_ends_at' => $gracePeriodEndDate,
            
            'next_billing_date' => $nextBillingDate,
            'billing_cycle' => $billingCycleForSubscription,
            'amount_paid' => $payment->amount,
            'asaas_subscription_id' => $payment->asaas_subscription_id,
            'payment_id' => $payment->id
        ]);
        
        // Atualizar o payment com o subscription_id
        $payment->update([
            'subscription_id' => $newSubscription->id
        ]);
        
        // Criar controle de uso para a nova assinatura
        // Para planos ilimitados (como Ouro), zerar todos os orçamentos herdados
        $inheritedBudgets = $plan->isUnlimited() ? 0 : 0; // Sempre 0 para novos pagamentos de assinatura
        $usageControl = \App\Models\UsageControl::getOrCreateForCurrentMonthWithReset(
            $payment->company_id,
            $newSubscription->id,
            $inheritedBudgets
        );
        
        Log::info('Nova assinatura criada e payment atualizado (fila)', [
            'payment_id' => $payment->id,
            'company_id' => $payment->company_id,
            'new_subscription_id' => $newSubscription->id,
            'plan_id' => $plan->id,
            'billing_cycle' => $billingCycleForSubscription,
            'amount' => $payment->amount,
            'usage_control_created' => true,
            'inherited_budgets' => $inheritedBudgets
        ]);
    }

    /**
     * Processar pagamento vencido
     */
    private function handlePaymentOverdue(Payment $payment, array $paymentData)
    {
        $payment->updateStatus($paymentData['status'], $paymentData);
        
        Log::info('Pagamento vencido processado via fila', [
            'payment_id' => $payment->id,
            'status' => $paymentData['status']
        ]);
    }

    /**
     * Processar pagamento cancelado/estornado
     */
    private function handlePaymentCancelled(Payment $payment, array $paymentData)
    {
        // Para eventos de cancelamento/exclusão, sempre definir status como 'cancelled'
        // independente do status que vem do Asaas
        $newStatus = 'cancelled';
        
        // Atualizar status e asaas_response com todo o payload do webhook
        $payment->updateStatus($newStatus, $this->payload);
        
        Log::info('Pagamento cancelado/estornado processado via webhook', [
            'payment_id' => $payment->id,
            'asaas_payment_id' => $payment->asaas_payment_id,
            'old_status' => $payment->getOriginal('status'),
            'new_status' => $newStatus,
            'asaas_status' => $paymentData['status'],
            'event' => $this->payload['event'] ?? 'unknown',
            'webhook_payload' => $this->payload
        ]);
    }

    /**
     * Processar pagamento de taxa de cancelamento
     */
    private function handleCancellationFeePayment(Payment $payment)
    {
        Log::info('Taxa de cancelamento paga via fila', [
            'payment_id' => $payment->id,
            'company_id' => $payment->company_id,
            'amount' => $payment->amount
        ]);
        
        // Aqui você pode implementar a lógica específica para taxa de cancelamento
        // Por exemplo, reativar a empresa ou permitir nova assinatura
    }
    


    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception)
    {
        Log::error('Job ProcessAsaasWebhook falhou após todas as tentativas', [
            'payload' => $this->payload,
            'error' => $exception->getMessage(),
            'trace' => $exception->getTraceAsString()
        ]);
    }
}
